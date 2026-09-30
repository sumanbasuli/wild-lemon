"""Focused browser checks alongside axe; not a screen-reader or conformance audit.

The 640/320 CSS-pixel viewports exercise the reflow space available when a
1280px desktop is zoomed to 200/400%. They do not automate browser-chrome zoom.
"""
import json
import os
from pathlib import Path
import re
import subprocess

BROWSER = os.environ.get('AGENT_BROWSER', 'agent-browser')
BASE = os.environ.get('QA_URL', 'http://localhost:8089')
OUTPUT = Path(os.environ.get('QA_OUTPUT', 'docs/qa/release-1.3'))

def run(*args):
    result = subprocess.run([BROWSER, '--session', 'wildlemon-a11y-review', '--json', *args],
                            text=True, capture_output=True, check=True)
    return json.loads(result.stdout)['data']

def js(code):
    return run('eval', code)['result']

def visit(path):
    run('open', BASE + path)
    js('document.fonts.ready.then(()=>true)')

def contrast(foreground, background):
    def luminance(color):
        values = [float(v) / 255 for v in re.findall(r'[\d.]+', color)[:3]]
        values = [v / 12.92 if v <= .04045 else ((v + .055) / 1.055) ** 2.4 for v in values]
        return sum(v * weight for v, weight in zip(values, [.2126, .7152, .0722]))
    first, second = sorted([luminance(foreground), luminance(background)])
    return round((second + .05) / (first + .05), 2)

results = {'skipLink': [], 'details': [], 'contrast': [], 'reflow': []}
for width in [320, 1440]:
    run('set', 'viewport', str(width), '900')
    visit('/pattern-gallery/')
    run('press', 'Tab')
    skip = js('''(()=>{const e=document.activeElement,b=e.getBoundingClientRect();
        return {text:e.textContent,href:e.getAttribute('href'),visible:b.width>0&&b.height>0&&b.top>=0}})()''')
    assert skip['text'] == 'Skip to content' and skip['visible'], skip
    run('press', 'Enter')
    assert js("location.hash==='#'+document.querySelector('main').id")
    # Core uses a native fragment link: subsequent Tab continues inside main.
    run('press', 'Tab')
    assert js("document.querySelector('main').contains(document.activeElement)")
    results['skipLink'].append({'width': width, 'visibleOnTab': True, 'keyboardContinuesInMain': True})
    selector = '.wl-weekend-detail:nth-of-type(2) summary'
    run('focus', selector)
    before = js("document.activeElement.parentElement.open")
    run('press', 'Space')
    assert js("document.activeElement.parentElement.open") != before
    run('press', 'Enter')
    assert js("document.activeElement.parentElement.open") == before
    results['details'].append({'width': width, 'spaceAndEnter': True})
    run('focus', '.wl-footer a')
    focus = js("(()=>{const s=getComputedStyle(document.activeElement);return {color:s.outlineColor,width:s.outlineWidth,style:s.outlineStyle,background:getComputedStyle(document.querySelector('.wl-footer')).backgroundColor}})()")
    focus['ratio'] = contrast(focus['color'], focus['background'])
    assert focus['style'] != 'none' and float(focus['width'].replace('px', '')) >= 2 and focus['ratio'] >= 3, focus
    results['contrast'].append({'type': 'footer keyboard focus', 'width': width, **focus})

# All axe incomplete findings in this fixture matrix are pagination arrow glyphs.
# Measure their actual foreground/background, plus the form boundaries axe omits.
for path in ['/', '/template-comments/', '/template-comments/comment-page-1/', '/?s=markup', '/missing-local-page/', '/template-password-protected/']:
    visit(path)
    nodes = js(r'''(()=>{
        const background=e=>{for(let p=e;p;p=p.parentElement){const c=getComputedStyle(p).backgroundColor;if(c!=='rgba(0, 0, 0, 0)'&&c!=='transparent')return c}return 'rgb(255, 255, 255)'};
        const arrows=[...document.querySelectorAll('[class*="pagination-"][class*="-arrow"],[class*="post-navigation-link__arrow-"]')];
        const fields=[...document.querySelectorAll('.wp-block-post-comments-form input[type=text],.wp-block-post-comments-form input[type=email],.wp-block-post-comments-form input[type=url],.wp-block-post-comments-form textarea,.post-password-form input[type=password],.wl-search-form .wp-block-search__inside-wrapper,.wl-404-search .wp-block-search__inside-wrapper')];
        const out=arrows.map(e=>({type:'arrow',selector:e.className,color:getComputedStyle(e).color,background:background(e)}));
        fields.forEach(e=>out.push({type:'field boundary',selector:e.id||e.className,color:getComputedStyle(e).borderTopColor,background:background(e)}));
        const header=document.querySelector('.wl-search-pill .wp-block-search__inside-wrapper');
        out.push({type:'header field boundary',color:getComputedStyle(header).boxShadow.match(/rgba?\([^)]+\)/)[0],background:background(header)});
        return out;
    })()''')
    for node in nodes:
        node.update(path=path, ratio=contrast(node['color'], node['background']))
        assert node['ratio'] >= (4.5 if node['type'] == 'arrow' else 3), node
        results['contrast'].append(node)

for width in [320, 640]:
    run('set', 'viewport', str(width), '900')
    for path in ['/', '/archive-regression/', '/pattern-gallery/', '/template-comments/', '/?s=markup', '/missing-local-page/']:
        visit(path)
        for spacing in [False, True]:
            if spacing:
                js("const s=document.createElement('style');s.textContent='body *{line-height:1.5!important;letter-spacing:.12em!important;word-spacing:.16em!important}p{margin-bottom:2em!important}';document.head.append(s)")
            measurement = js('''(()=>{const all=[...document.querySelectorAll('main h1,main h2,main h3,main p,.wl-header button')].filter(e=>e.getClientRects().length);
                return {overflow:document.documentElement.scrollWidth-innerWidth,clipped:all.filter(e=>getComputedStyle(e).overflow==='hidden'&&(e.scrollHeight>e.clientHeight+1||e.scrollWidth>e.clientWidth+1)).map(e=>e.className)}})()''')
            assert measurement['overflow'] <= 1 and not measurement['clipped'], measurement
            results['reflow'].append({'width': width, 'path': path, 'textSpacing': spacing, **measurement})

OUTPUT.mkdir(parents=True, exist_ok=True)
(OUTPUT / 'accessibility-review.json').write_text(json.dumps(results, indent=2) + '\n')
run('close')
print(json.dumps({key: len(value) for key, value in results.items()}))
