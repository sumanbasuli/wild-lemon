import subprocess,json,sys
import os
B=os.environ.get('AGENT_BROWSER', 'agent-browser')
def run(*args):
 p=subprocess.run([B,'--session','wildlemon','--json',*args],capture_output=True,text=True)
 if p.returncode: raise RuntimeError(p.stderr+p.stdout)
 return json.loads(p.stdout)['data']
paths=['/','/pattern-gallery/','/markup-html-tags-and-formatting/','/edge-case-nested-and-mixed-lists/','/edge-case-many-categories/','/edge-case-many-tags/','/column-blocks/','/template-comments/','/template-password-protected/','/?s=markup','/?s=zzzznothing','/missing-local-page/']
js='''(()=>{const vw=document.documentElement.clientWidth;return {width:innerWidth,overflow:document.documentElement.scrollWidth-vw,offenders:[...document.querySelectorAll('main *,header *,footer *')].filter(e=>{let r=e.getBoundingClientRect(),s=getComputedStyle(e);return r.width>0&&(r.right>vw+1||r.left< -1)&&s.position!=='absolute'&&s.position!=='fixed'&&s.visibility!=='hidden'&&!e.closest('.wp-block-navigation__responsive-container:not(.is-menu-open)')}).slice(0,7).map(e=>({tag:e.tagName,cls:String(e.className).slice(0,90),width:Math.round(e.getBoundingClientRect().width)})),h1:document.querySelectorAll('main h1').length}})()'''
results=[]
for width in [320,390,768,1024,1440]:
 run('set','viewport',str(width),'900')
 for path in paths:
  run('open','http://localhost:8088'+path)
  r=run('eval',js)['result'];r['path']=path;results.append(r)
  if r['overflow']>1: print(json.dumps(r),flush=True)
json.dump(results,open(sys.argv[1],'w'),indent=2)
print('Checked',len(results),'cases; overflow cases:',sum(r['overflow']>1 for r in results),flush=True)
