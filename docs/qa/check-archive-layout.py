"""Regression for category/title competition, narrow containers, RTL and text spacing.

Requires the QA archive-regression page containing the official Many Categories post.
Only the test browser DOM is changed; no posts, terms or options are saved.
"""
import json
import os
import pathlib
import subprocess

browser=os.environ.get('AGENT_BROWSER','agent-browser')
base=os.environ.get('QA_URL','http://localhost:8089')
output=pathlib.Path(os.environ.get('QA_OUTPUT','docs/qa/official-2026-09-30'))
session='wildlemon-archive-regression'

def run(*args):
    p=subprocess.run([browser,'--session',session,'--json',*args],text=True,capture_output=True)
    if p.returncode:raise RuntimeError(p.stdout+p.stderr)
    return json.loads(p.stdout)['data']

def js(code):return run('eval',code)['result']

results=[]
for width,container in [(320,None),(390,None),(640,None),(720,None),(960,None),(1440,None),
                         (1440,240),(1440,320),(1440,480),(1440,600),(1440,700),(1440,780)]:
    run('set','viewport',str(width),'900')
    run('open',base+'/archive-regression/')
    js('''window.originalTerms=document.querySelector('.wl-row-link .wp-block-post-terms').innerHTML;
        document.querySelector('.wl-row-link .wp-block-post-title a').textContent='Category Hierarchy';''')
    if container:js(f"document.querySelector('.wl-archive-list').style.width='{container}px'")
    for count in [1,2,3,4,63]:
        js(f'''(()=>{{const terms=document.querySelector('.wl-row-link .wp-block-post-terms');
            terms.innerHTML=window.originalTerms;
            const links=[...terms.querySelectorAll('a')].slice(0,{count});
            terms.replaceChildren(...links.flatMap((a,i)=>i?[document.createTextNode(', '),a]:[a]));
        }})()''')
        r=js('''(async()=>{await document.fonts.ready;const row=document.querySelector('.wl-row-link');
            const t=row.querySelector('.wp-block-post-title'),terms=row.querySelector('.wp-block-post-terms');
            const a=t.getBoundingClientRect(),b=terms.getBoundingClientRect();
            return {row:row.clientWidth,title:t.clientWidth,categories:terms.clientWidth,
                categoryCount:terms.querySelectorAll('a').length,columns:getComputedStyle(row).gridTemplateColumns,
                overlap:a.left<b.right&&a.right>b.left&&a.top<b.bottom&&a.bottom>b.top,
                titleHeight:t.clientHeight,overflow:document.documentElement.scrollWidth-innerWidth};})()''')
        r.update(viewport=width,container=container,count=count)
        assert r['title']>=min(160,r['row']*.6) and not r['overlap'] and r['overflow']<=1,r
        assert r['categoryCount']==count,r
        if count>=4:assert r['title']>=r['row']-140,r
        results.append(r)
    for variation,code in [('rtl',"document.documentElement.dir='rtl'"),('text-spacing',"document.documentElement.dir='ltr';const s=document.createElement('style');s.textContent='.wl-row-link * {line-height:1.5!important;letter-spacing:.12em!important;word-spacing:.16em!important}';document.head.append(s)")]:
        js(code)
        assert js('document.documentElement.scrollWidth-innerWidth')<=1
        results.append({'viewport':width,'container':container,'variation':variation,'overflow':0})
(output/'archive-regression.json').write_text(json.dumps(results,indent=2)+'\n')
run('close')
print(f'{len(results)} archive cases passed')
