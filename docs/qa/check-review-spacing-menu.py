"""Verify the reported spacing and native current-menu states on both QA sites."""
import json
import os
from pathlib import Path
import subprocess

BROWSER = os.environ.get('AGENT_BROWSER', 'agent-browser')
OUTPUT = Path(os.environ.get('QA_OUTPUT', 'docs/qa/review-2026-10-28'))
SESSION = 'wildlemon-review-oct28'


def run(*args):
    result = subprocess.run([BROWSER, '--session', SESSION, '--json', *args], capture_output=True, text=True, check=True)
    response = json.loads(result.stdout)
    if not response['success']:
        raise RuntimeError(response)
    return response['data']


def evaluate(source):
    return run('eval', source)['result']


MEASURE = '''document.fonts.ready.then(()=>{
 const content=document.querySelector('main > .wp-block-post-content');
 const previous=content?.previousElementSibling;
 const first=content?.firstElementChild;
 const r=e=>{const b=e.getBoundingClientRect();return {top:b.top,bottom:b.bottom,left:b.left,right:b.right,width:b.width,height:b.height};};
 return {url:location.href,width:innerWidth,overflow:document.documentElement.scrollWidth-innerWidth,
  hasContent:!!content,first:first&&r(first),previous:previous&&r(previous),
  gap:first&&previous?r(first).top-r(previous).bottom:null,
  padding:content&&getComputedStyle(content).paddingTop,
  current:[...document.querySelectorAll('.wl-header a[aria-current="page"]')].map(a=>({text:a.textContent,href:a.href,decoration:getComputedStyle(a).textDecorationLine,thickness:getComputedStyle(a).textDecorationThickness,background:getComputedStyle(a).backgroundColor}))};
})'''

paths = [
    '/wl-review-post-columns/', '/wl-review-post-paragraph/', '/wl-review-post-heading/',
    '/wl-review-post-featured/', '/wl-review-page-heading/', '/wl-review-page-paragraph/',
    '/wl-review-page-featured/', '/wl-review-page-empty/',
]
results = []
menus = []
try:
    for port in (8089, 8090):
        label = 'current' if port == 8089 else 'minimum'
        for width in (320, 768, 1440):
            run('set', 'viewport', str(width), '900')
            for path in paths:
                run('open', f'http://localhost:{port}' + path)
                data = evaluate(MEASURE)
                if path.endswith('page-empty/'):
                    assert not data['hasContent'], data
                else:
                    assert 23 <= data['gap'] <= 57, data
                assert data['overflow'] <= 1, data
                for current in data['current']:
                    assert current['decoration'] == 'underline' and current['thickness'] == '2px', data
                results.append(data)
                if width in (320, 1440) and path in ('/wl-review-post-columns/', '/wl-review-page-heading/'):
                    name = 'post' if 'post-columns' in path else 'page'
                    run('screenshot', str(OUTPUT / f'{label}-{name}-after-{width}.png'))
            print(json.dumps({'site': label, 'width': width, 'reportedCases': 8, 'passed': True}), flush=True)

        for width in (320, 1440):
            run('set', 'viewport', str(width), '900')
            run('open', f'http://localhost:{port}/wl-review-post-columns/')
            evaluate('document.fonts.ready.then(()=>true)')
            if width == 320:
                run('click', 'button[aria-label="Open menu"]')
            else:
                run('click', 'button[aria-label="Test Page 26th October submenu"]')
            state = evaluate('''(()=>{const a=document.querySelector('.wl-header a[aria-current="page"]');const s=a.closest('ul').querySelector('a[href$="/wl-review-post-paragraph/"]');return {active:a.textContent,activeHref:a.href,visible:a.getBoundingClientRect().height>0,decoration:getComputedStyle(a).textDecorationLine,background:getComputedStyle(a).backgroundColor,siblingDecoration:getComputedStyle(s).textDecorationLine,siblingBackground:getComputedStyle(s).backgroundColor}})()''')
            assert state['visible'] and state['active'] == 'Test 2', state
            assert state['decoration'] == 'underline' and state['background'] == 'rgb(246, 236, 194)', state
            assert state['siblingDecoration'] == 'none' and state['siblingBackground'] != state['background'], state
            state.update(port=port, width=width)
            menus.append(state)
            run('screenshot', str(OUTPUT / f'{label}-selected-menu-{width}.png'))
            evaluate("document.querySelector('.wl-header a[href$=\"/wl-review-post-paragraph/\"]').focus()")
            run('press', 'Enter')
            destination = evaluate("({path:location.pathname,title:document.querySelector('h1')?.textContent,current:[...document.querySelectorAll('.wl-header a[aria-current=page]')].map(a=>a.textContent)})")
            assert destination['path'] == '/wl-review-post-paragraph/' and destination['current'] == ['Test 3'], destination
            state['keyboardDestination'] = destination

        run('set', 'viewport', '1440', '900')
        for path, name in (('/sample-page/', 'page'), ('/hello-world/', 'post')):
            run('open', f'http://localhost:{port}' + path)
            data = evaluate(MEASURE)
            assert data['gap'] == 56, data
            if port == 8090:
                run('screenshot', str(OUTPUT / f'minimum-{name}-after.png'))
finally:
    (OUTPUT / 'spacing-menu.json').write_text(json.dumps({'spacing': results, 'menus': menus}, indent=2) + '\n')
    run('close')
print(json.dumps({'spacingCases': len(results), 'selectedMenuFlows': len(menus), 'passed': True}), flush=True)
