import subprocess,json
import os
B=os.environ.get('AGENT_BROWSER', 'agent-browser')
def run(*args,session='wildlemon'):
 p=subprocess.run([B,'--session',session,'--json',*args],capture_output=True,text=True,check=True)
 return json.loads(p.stdout)['data']
def js(code): return run('eval',code)['result']
results={}
for width in [320,390,768]:
 run('set','viewport',str(width),'844');run('open','http://localhost:8088/pattern-gallery/')
 r=js('''(()=>{let selectors=['.wp-block-navigation__responsive-container-open','.wl-search-pill button'];return selectors.map(s=>{let e=document.querySelector(s),b=e.getBoundingClientRect(),v=(e.querySelector('svg')||e).getBoundingClientRect();return {width:b.width,height:b.height,verticalOffset:(v.top+v.height/2)-(b.top+b.height/2),center:b.top+b.height/2}})})()''')
 assert all(x['width']>=44 and x['height']==44 and abs(x['verticalOffset'])<=1 for x in r),r
 assert abs(r[0]['center']-r[1]['center'])<=1,r
 run('click','button[aria-label="Open menu"]')
 menu=js('''(()=>{let e=document.querySelector('.is-menu-open');return {open:!!e,overflow:document.documentElement.scrollWidth-innerWidth,scrollable:e.scrollHeight>e.clientHeight&&getComputedStyle(e).overflowY==='auto',focusInside:e.contains(document.activeElement)}})()''')
 assert menu['open'] and menu['overflow']==0 and menu['focusInside'] and menu['scrollable'],menu
 run('press','Shift+Tab');run('press','Shift+Tab')
 assert js("document.querySelector('.is-menu-open').contains(document.activeElement)")
 run('press','Escape')
 assert js("!document.querySelector('.is-menu-open') && document.activeElement.getAttribute('aria-label')==='Open menu'")
 results[str(width)]={'controls':r,'menu':menu,'escapeReturnsFocus':True,'focusContained':True}
run('open','http://localhost:8088/pattern-gallery/')
results['rtl']=js("document.documentElement.dir='rtl';({overflow:document.documentElement.scrollWidth-innerWidth})")
run('open','http://localhost:8088/pattern-gallery/')
results['textSpacing']=js("const s=document.createElement('style');s.textContent='p,li,h1,h2,h3 {line-height:1.5 !important;letter-spacing:.12em !important;word-spacing:.16em !important} p {margin-bottom:2em !important}';document.head.append(s);({overflow:document.documentElement.scrollWidth-innerWidth})")
run('open','http://localhost:8088/pattern-gallery/')
results['longTitle']=js("document.querySelector('header .wp-block-site-title a').textContent='WildLemonAnExceptionallyLongUnbrokenSiteTitleForTesting';({overflow:document.documentElement.scrollWidth-innerWidth})")
run('set','viewport','1440','900');run('open','http://localhost:8088/')
results['archivePage1']=js("({posts:[...document.querySelectorAll('.wl-archive-list .wp-block-post')].map(e=>e.className.match(/post-\\d+/)?.[0]),next:document.querySelector('.wp-block-query-pagination-next')?.href})")
run('open',results['archivePage1']['next'])
results['archivePage2']=js("({posts:[...document.querySelectorAll('.wl-archive-list .wp-block-post')].map(e=>e.className.match(/post-\\d+/)?.[0]),previous:document.querySelector('.wp-block-query-pagination-previous')?.href})")
assert results['archivePage2']['posts'] and results['archivePage2']['previous']
assert all(results[k]['overflow']==0 for k in ['rtl','textSpacing','longTitle'])
open('docs/qa/interaction-checks.json','w').write(json.dumps(results,indent=2))
print(json.dumps(results,indent=2))
