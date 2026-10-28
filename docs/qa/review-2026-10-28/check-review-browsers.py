"""Focused cross-engine checks of the October spacing/current-navigation reports."""
import asyncio
import hashlib
import importlib.metadata
import json
from pathlib import Path

from playwright.async_api import async_playwright

OUTPUT = Path('docs/qa/review-2026-10-28/browser-focused.json')
BASES = ('http://localhost:8089', 'http://localhost:8090')
PATHS = ('/wl-review-post-columns/', '/wl-review-page-heading/', '/wl-review-post-featured/')
WIDTHS = (320, 768, 1440)
REPORT = {'playwrightVersion': importlib.metadata.version('playwright'), 'sourceSha256': {}, 'layout': [], 'interactions': [], 'browserVersions': {}, 'failures': []}

for filename in ('style.css', 'templates/page.html', 'templates/single.html', 'functions.php'):
    REPORT['sourceSha256'][filename] = hashlib.sha256(Path(filename).read_bytes()).hexdigest()

MEASURE = '''() => {
 const content=document.querySelector('main .wp-block-post-content');
 const first=content?.firstElementChild, previous=content?.previousElementSibling;
 const links=[...document.querySelectorAll('.wl-header .wp-block-navigation a[aria-current="page"]')];
 const gap=first.getBoundingClientRect().top-previous.getBoundingClientRect().bottom;
 return {url:location.href,overflow:document.documentElement.scrollWidth-innerWidth,
  gap,paddingTop:parseFloat(getComputedStyle(content).paddingTop),marginTop:getComputedStyle(content).marginTop,
  firstTag:first.tagName,previousClass:previous.className,
  selected:links.map(a=>({path:new URL(a.href).pathname,text:a.textContent.trim(),
   line:getComputedStyle(a).textDecorationLine,thickness:getComputedStyle(a).textDecorationThickness,
   background:getComputedStyle(a).backgroundColor,nested:!!a.closest('.wp-block-navigation__submenu-container')}))};
}'''


def save():
    OUTPUT.write_text(json.dumps(REPORT, indent=2) + '\n')


async def ready(page):
    await page.evaluate('document.fonts.ready')


async def selected(page, path):
    result = await page.evaluate(MEASURE)
    assert [link['path'] for link in result['selected']] == [path], result
    link = result['selected'][0]
    assert 'underline' in link['line'] and link['thickness'] == '2px', result
    if link['nested']:
        assert link['background'] not in ('transparent', 'rgba(0, 0, 0, 0)'), result
    return result['selected']


async def interactions(page, base, width, browser_name):
    response = await page.goto(base + '/wl-review-post-columns/', wait_until='domcontentloaded')
    assert response.status == 200
    await ready(page)
    mobile = width <= 900
    if mobile:
        opener = page.get_by_role('button', name='Open menu', exact=True)
        await opener.focus()
        await page.keyboard.press('Enter')
        assert await page.locator('.is-menu-open').count() == 1
    else:
        toggle = page.get_by_role('button', name='Test Page 26th October submenu', exact=True)
        await toggle.focus()
        await page.keyboard.press('Enter')
        assert await toggle.get_attribute('aria-expanded') == 'true'
        await page.keyboard.press('Escape')
        assert await toggle.get_attribute('aria-expanded') == 'false'
        assert await toggle.evaluate('(e)=>document.activeElement===e')
        await page.keyboard.press('Enter')

    current = page.locator('.wl-header .wp-block-navigation a[aria-current="page"]')
    assert await current.is_visible()
    before = await selected(page, '/wl-review-post-columns/')
    target = page.locator('.wl-header .wp-block-navigation a[href$="/wl-review-post-paragraph/"]')
    await target.focus()
    await page.keyboard.press('Enter')
    await page.wait_for_url(base + '/wl-review-post-paragraph/')
    await ready(page)
    after = await selected(page, '/wl-review-post-paragraph/')
    if mobile:
        opener = page.get_by_role('button', name='Open menu', exact=True)
        await opener.focus()
        await page.keyboard.press('Enter')
        assert await page.locator('.is-menu-open').count() == 1
        await page.keyboard.press('Escape')
        await page.wait_for_function("document.activeElement?.getAttribute('aria-label')==='Open menu'")
        assert await opener.evaluate('(e)=>e.matches(":focus-visible")')
    record = {'browser': browser_name, 'base': base, 'width': width, 'mobileMenu': mobile,
              'keyboardNavigation': True, 'escapeFocus': True, 'selectedBefore': before, 'selectedAfter': after, 'pass': True}
    REPORT['interactions'].append(record)
    save()
    print(json.dumps({'interaction': browser_name, 'base': base, 'width': width, 'pass': True}), flush=True)


async def main():
    async with async_playwright() as playwright:
        for name in ('firefox', 'webkit'):
            browser = await getattr(playwright, name).launch()
            REPORT['browserVersions'][name] = browser.version
            page = await browser.new_page()
            errors = []
            page.on('pageerror', lambda error: errors.append(str(error)))
            try:
                for base in BASES:
                    for width in WIDTHS:
                        await page.set_viewport_size({'width': width, 'height': 900})
                        for path in PATHS:
                            errors.clear()
                            response = await page.goto(base + path, wait_until='domcontentloaded')
                            assert response.status == 200, (base, path, response.status)
                            await ready(page)
                            result = await page.evaluate(MEASURE)
                            result.update(browser=name, version=browser.version, base=base, path=path, width=width, pageErrors=list(errors))
                            assert result['overflow'] <= 1, result
                            assert 23.5 <= result['gap'] <= 56.5, result
                            assert abs(result['gap'] - result['paddingTop']) <= .75, result
                            assert not errors, result
                            if path != '/wl-review-post-featured/':
                                await selected(page, path)
                            else:
                                assert not result['selected'], result
                            result['pass'] = True
                            REPORT['layout'].append(result)
                            save()
                            print(json.dumps({'layout': name, 'base': base, 'path': path, 'width': width, 'gap': result['gap'], 'pass': True}), flush=True)
                        await interactions(page, base, width, name)
            except Exception as error:
                REPORT['failures'].append({'browser': name, 'base': base, 'width': width, 'error': str(error)})
                save()
                raise
            finally:
                await browser.close()
    REPORT['passed'] = not REPORT['failures']
    save()
    print(json.dumps({'layouts': len(REPORT['layout']), 'interactions': len(REPORT['interactions']), 'browsers': REPORT['browserVersions'], 'passed': REPORT['passed']}), flush=True)


asyncio.run(main())
