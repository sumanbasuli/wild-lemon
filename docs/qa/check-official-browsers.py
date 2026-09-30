"""Additional Firefox/WebKit checks; install Playwright and its two browsers first."""
import asyncio
import json
import os
import pathlib
from playwright.async_api import async_playwright

base = os.environ.get('QA_URL', 'http://localhost:8089')
output = pathlib.Path(os.environ.get('QA_OUTPUT', 'docs/qa/official-2026-09-30'))
paths = ['/', '/archive-regression/', '/pattern-gallery/', '/markup-image-alignment/',
         '/media-category-blocks/', '/template-comments/', '/edge-case-no-title/',
         '/about/page-with-comments/', '/?s=markup']
measure = '''() => ({overflow:document.documentElement.scrollWidth-innerWidth,
    archive:[...document.querySelectorAll('.wl-row-link')].map(e=>({
        width:e.clientWidth,title:e.querySelector('.wp-block-post-title')?.clientWidth,
        terms:e.querySelectorAll('.wp-block-post-terms a').length})),
    comments:[...document.querySelectorAll('.wp-block-comment-content')].map(e=>e.clientWidth)})'''

async def main():
    results=[]
    async with async_playwright() as p:
        for name in ['firefox','webkit']:
            browser=await getattr(p,name).launch()
            page=await browser.new_page()
            for width in [320,390,768,1024,1440]:
                await page.set_viewport_size({'width':width,'height':900})
                for path in paths:
                    await page.goto(base+path,wait_until='domcontentloaded')
                    await page.evaluate('document.fonts.ready')
                    result=await page.evaluate(measure)
                    result.update(browser=name,version=browser.version,path=path,width=width)
                    results.append(result)
                    if result['overflow']>1:print(json.dumps(result),flush=True)
                # Exercise the actual native mobile menu and Search Interactivity API.
                if width in [320,768]:
                    await page.goto(base+'/pattern-gallery/',wait_until='domcontentloaded')
                    await page.get_by_role('button',name='Open menu',exact=True).focus()
                    await page.keyboard.press('Enter')
                    assert await page.locator('.is-menu-open').count()==1
                    await page.keyboard.press('Escape')
                    await page.wait_for_function("document.activeElement?.getAttribute('aria-label')==='Open menu'")
                    await page.locator('.wl-search-pill button').click()
                    await page.locator('.wl-search-pill input[type="search"]').wait_for(state='visible')
                    await page.wait_for_function("document.activeElement?.matches('.wl-search-pill input[type=search]')")
                    await page.keyboard.press('Escape')
                    assert await page.locator('.wp-block-search__searchfield-hidden').count()==1
                    results.append({'browser':name,'width':width,'menuEscapeFocus':True,'searchOpenEscape':True})
            await browser.close()
            print(name+' completed',flush=True)
    (output/'browsers.json').write_text(json.dumps(results,indent=2)+'\n')
    assert all(r.get('overflow',0)<=1 for r in results)

asyncio.run(main())
