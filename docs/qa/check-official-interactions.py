"""Native menu, query, comments, password and multipage flows on official fixtures."""
import subprocess,json,pathlib,os
B=os.environ.get('AGENT_BROWSER','agent-browser')
BASE=os.environ.get('QA_URL','http://localhost:8089')
OUTPUT=pathlib.Path(os.environ.get('QA_OUTPUT','docs/qa/official-2026-09-30/interactions.json'))
def run(*a):
 p=subprocess.run([B,'--session','wildlemon-qa-controls','--json',*a],capture_output=True,text=True)
 if p.returncode:raise RuntimeError(p.stdout+p.stderr)
 return json.loads(p.stdout)['data']
def js(code):return run('eval',code)['result']
def visit(path):run('open',BASE+path)
out={}
run("cookies","clear")
for w in [320,390,768]:
 run('set','viewport',str(w),'844');visit('/pattern-gallery/')
 controls=js("[...document.querySelectorAll('.wp-block-navigation__responsive-container-open,.wl-search-pill button')].map(e=>{const b=e.getBoundingClientRect(),i=(e.querySelector('svg')||e).getBoundingClientRect();return {width:b.width,height:b.height,offset:(i.top+i.height/2)-(b.top+b.height/2)}})")
 assert all(r['width']>=44 and r['height']==44 and abs(r['offset'])<1 for r in controls),controls
 run('focus','button[aria-label="Open menu"]');run('press','Enter')
 menu=js("(()=>{const e=document.querySelector('.is-menu-open');return {scrollable:e.scrollHeight>e.clientHeight&&getComputedStyle(e).overflowY==='auto',links:e.querySelectorAll('a').length,inside:e.contains(document.activeElement),overflow:document.documentElement.scrollWidth-innerWidth,minLinkHeight:Math.min(...[...e.querySelectorAll('.wp-block-navigation-item__content')].map(a=>a.getBoundingClientRect().height))}})()")
 assert menu['minLinkHeight']>=43.99 and menu['inside'] and menu['scrollable'] and menu['links']>=18 and menu['overflow']==0,menu
 run('press','Shift+Tab');run('press','Shift+Tab');assert js("document.querySelector('.is-menu-open').contains(document.activeElement)")
 run('press','Escape');assert js("!document.querySelector('.is-menu-open')&&document.activeElement.getAttribute('aria-label')==='Open menu'")
 out[str(w)]={'controls':controls,'menu':menu,'focusTrap':True,'escapeRestoresFocus':True}
# Native pagination must actually navigate to a different set of posts.
visit('/');first=js("[...document.querySelectorAll('.wl-archive-list .wp-block-post')].map(e=>e.className.match(/post-[0-9]+/)[0])")
run('click','.wl-archive-list .wp-block-query-pagination-next')
second=js("[...document.querySelectorAll('.wl-archive-list .wp-block-post')].map(e=>e.className.match(/post-[0-9]+/)[0])")
assert first and second and not set(first)&set(second),(first,second)
run('click','.wl-archive-list .wp-block-query-pagination-previous');assert js("new URL(location.href).searchParams.get('query-11-page')")=='1'
out['queryPagination']={'first':first,'second':second,'previousWorks':True}
visit('/?query-11-page=999');assert js("!document.querySelector('.wl-archive-list .wp-block-query-pagination')&&!!document.querySelector('.wl-archive-list .wp-block-query-no-results')")
out['outOfRange']={'pagination':False,'emptyMessage':True}
# Untitled posts have a visible native date permalink.
visit('/?s=%22This+post+has+no+title%22');assert js("!!document.querySelector('.wp-block-post-date a[href*=edge-case-no-title]')")
run('click','.wp-block-post-date a[href*=edge-case-no-title]');assert js("location.pathname")=='/edge-case-no-title/'
out['untitledPost']={'dateLinkWorks':True}
# Pages display existing comments; closed pages without comments stay quiet.
visit('/about/page-with-comments/');assert js("!!document.querySelector('.wp-block-comments')&&!!document.querySelector('.wp-block-comment-template li')")
visit('/about/page-with-comments-disabled/');assert js("!document.querySelector('.wp-block-post-comments-form')")
out['pageComments']={'existingVisible':True,'closedFormAbsent':True}
# Threaded comments, native reply/cancel, and older/newer pagination.
run('set','viewport','320','900');visit('/template-comments/')
widths=js("[...document.querySelectorAll('.wp-block-comment-content')].map(e=>e.clientWidth)");assert min(widths)>=160,widths
run('click','.comment-reply-link:first-of-type')
assert js("document.querySelector('#comment_parent').value!=='0'")
run('click','#cancel-comment-reply-link');assert js("document.querySelector('#comment_parent').value==='0'")
ids=js("[...document.querySelectorAll('.wp-block-comment-template li')].map(e=>e.id)")
run('click','.wp-block-comments-pagination-previous')
older=js("[...document.querySelectorAll('.wp-block-comment-template li')].map(e=>e.id)");assert older and not set(ids)&set(older)
run('click','.wp-block-comments-pagination-next')
out['comments']={'minimumTextWidth':min(widths),'replyCancel':True,'olderNewer':True}
# Native password form unlocks the official password-protected fixture.
visit('/template-password-protected/');run('fill','.post-password-form input[type=password]','enter');run('click','.post-password-form input[type=submit]')
assert js("!document.querySelector('.post-password-form')")
out['password']={'unlocked':True}
# Content split with nextpage remains navigable.
visit('/template-paginated/');run('snapshot','-i')
links=js("[...document.querySelectorAll('.post-nav-links a')].map(e=>e.href)")
if not links:links=js("[...document.querySelectorAll('.page-links a')].map(e=>e.href)")
assert links,'Missing native multipage post links'
run('open',links[0]);out['multipagePost']={'links':links,'navigated':js('location.href')}
OUTPUT.write_text(json.dumps(out,indent=2)+'\n')
run('close');print(json.dumps(out,indent=2))
