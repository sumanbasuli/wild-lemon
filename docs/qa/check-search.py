"""Verify the real core Search block in the local header with agent-browser."""
import json
import os
from pathlib import Path
import subprocess

B = os.environ.get('AGENT_BROWSER', 'agent-browser')
BASE = os.environ.get('QA_URL', 'http://localhost:8088')
TERM = os.environ.get('QA_SEARCH_TERM', 'garden')
OUTPUT = os.environ.get('QA_OUTPUT', 'docs/qa/search-checks.json')

def browser(*args):
    result = subprocess.run([B, '--session', 'wildlemon-search', '--json', *args], capture_output=True, text=True, check=True)
    return json.loads(result.stdout)['data']

def evaluate(code):
    return browser('eval', code)['result']

GEOMETRY = """(()=>{const form=document.querySelector('.wl-search-pill'), field=form.querySelector('.wp-block-search__inside-wrapper'), button=form.querySelector('button'), input=form.querySelector('input');return {header:document.querySelector('.wl-header').getBoundingClientRect().toJSON(),mainTop:document.querySelector('main').getBoundingClientRect().top,field:field.getBoundingClientRect().toJSON(),button:button.getBoundingClientRect().toJSON(),label:button.textContent.trim(),focused:document.activeElement===input,expanded:button.getAttribute('aria-expanded'),overflow:document.documentElement.scrollWidth-innerWidth,navVisible:getComputedStyle(document.querySelector('.wl-header .wp-block-navigation')).visibility,formOverflow:getComputedStyle(form).overflow,fieldOverflow:getComputedStyle(field).overflow}})()"""

def check_open(before, after):
    assert after['label'] == 'Search' and after['focused'] and after['expanded'] == 'true', after
    assert after['overflow'] == 0 and after['field']['left'] >= 0 and after['field']['right'] <= before['header']['width'] + 1, after
    for key in ['x', 'y', 'height']:
        assert abs(before['button'][key] - after['button'][key]) < 1, (key, before, after)
    assert abs(before['header']['height'] - after['header']['height']) < 1
    assert abs(before['mainTop'] - after['mainTop']) < 1
    assert after['field']['top'] >= after['header']['top'] and after['field']['bottom'] <= after['header']['bottom']

results = []
for width in [320, 390, 600, 768, 900, 901, 1024, 1440]:
    browser('set', 'viewport', str(width), '900')
    browser('open', BASE+'/')
    evaluate('document.fonts.ready.then(()=>true)')
    before = evaluate(GEOMETRY)
    assert before['label'] == 'Search' and before['formOverflow'] == 'visible' and before['fieldOverflow'] == 'visible'
    evaluate("""(()=>{window.wlSearchFrames=[];const end=performance.now()+700;function sample(){const h=document.querySelector('.wl-header').getBoundingClientRect(),b=document.querySelector('.wl-search-pill button').getBoundingClientRect(),f=document.querySelector('.wl-search-pill .wp-block-search__inside-wrapper').getBoundingClientRect();window.wlSearchFrames.push({height:h.height,y:b.y,x:b.x,width:f.width,mainTop:document.querySelector('main').getBoundingClientRect().top});if(performance.now()<end)requestAnimationFrame(sample)}requestAnimationFrame(sample)})()""")
    browser('click', '.wl-search-pill button')
    browser('wait', '350')
    after = evaluate(GEOMETRY)
    check_open(before, after)
    frames = evaluate('window.wlSearchFrames')
    assert all(abs(f['height']-before['header']['height'])<1 and abs(f['mainTop']-before['mainTop'])<1 and abs(f['y']-before['button']['y'])<1 for f in frames)
    assert any(before['field']['width']+2 < f['width'] < after['field']['width']-2 for f in frames), 'Expansion did not animate'
    browser('press', 'Escape')
    browser('wait', '350')
    assert evaluate("document.activeElement===document.querySelector('.wl-search-pill button') && document.activeElement.getAttribute('aria-expanded')==='false'")
    assert evaluate(GEOMETRY)['navVisible'] == 'visible'
    browser('click', '.wl-search-pill button')
    browser('wait', '350')
    browser('mouse', 'move', '2', str(int(before['header']['bottom'])+16))
    browser('mouse', 'down')
    browser('mouse', 'up')
    browser('wait', '350')
    assert evaluate(GEOMETRY)['expanded'] == 'false', 'Clicking outside did not close search'
    results.append({'width':width,'headerHeight':after['header']['height'],'fieldWidth':after['field']['width'],'layoutShift':0,'focusedOnOpen':True,'escapeRestoresFocus':True,'outsideClickCloses':True,'animated':True})

# Long brand names and RTL direction must not push the expanded field off-screen.
for width, direction in [(320, 'ltr'), (901, 'ltr'), (390, 'rtl'), (1440, 'rtl')]:
    browser('set','viewport',str(width),'900');browser('open',BASE+'/')
    evaluate("document.documentElement.dir="+json.dumps(direction))
    if direction=='ltr':
        evaluate("document.querySelector('.wl-header .wp-block-site-title a').textContent='Wild Lemon — a very long journal name for responsive testing'")
    before=evaluate(GEOMETRY);browser('click','.wl-search-pill button');browser('wait','350');after=evaluate(GEOMETRY);check_open(before,after)
    results.append({'width':width,'direction':direction,'longTitle':direction=='ltr','layoutShift':0,'overflow':after['overflow']})

# Both Enter and the labeled submit button still use WordPress's normal search.
for width, method in [(390, 'enter'), (1440, 'button')]:
    browser('set','viewport',str(width),'900');browser('open',BASE+'/')
    browser('click','.wl-search-pill button');browser('fill','.wl-search-pill input',TERM)
    browser('press','Enter') if method=='enter' else browser('click','.wl-search-pill button')
    browser('wait','200')
    result=evaluate("({url:location.href,posts:document.querySelectorAll('main .wp-block-post').length})")
    assert ('?s='+TERM) in result['url'] and result['posts']>0, result
    results.append({'width':width,'submit':method,**result})

browser('set','media','light','reduced-motion');browser('open',BASE+'/')
reduced=evaluate("({enabled:matchMedia('(prefers-reduced-motion: reduce)').matches,durations:[...document.querySelectorAll('.wl-search-pill .wp-block-search__inside-wrapper,.wl-search-pill input,.wl-header .wp-block-navigation')].map(e=>getComputedStyle(e).transitionDuration)})")
assert reduced['enabled'] and all(value=='0s' for value in reduced['durations']), reduced
results.append({'reducedMotion':reduced})
browser('set','media','light')
Path(OUTPUT).write_text(json.dumps(results,indent=2))
print(json.dumps(results,indent=2))
