"""Axe checks on official fixture routes after transitions settle."""
import subprocess,json,pathlib,os
B=os.environ.get('AGENT_BROWSER','agent-browser')
BASE=os.environ.get('QA_URL','http://localhost:8089')
OUTPUT=pathlib.Path(os.environ.get('QA_OUTPUT','docs/qa/official-2026-09-30/accessibility.json'))
def run(*a):
 p=subprocess.run([B,'--session','wildlemon-qa-a11y','--json',*a],capture_output=True,text=True,check=True);return json.loads(p.stdout)['data']
def settle():
 run('eval','Promise.all([document.fonts.ready,...document.getAnimations().map(a=>a.finished)]).then(()=>true)')
results=[]
for w in [320,1440]:
 run('set','viewport',str(w),'900')
 for path in ['/','/archive-regression/','/pattern-gallery/','/markup-html-tags-and-formatting/','/markup-image-alignment/','/template-comments/','/template-comments/comment-page-1/','/about/page-with-comments/','/template-password-protected/','/edge-case-no-title/','/?s=markup','/?s=zzzznothing','/missing-local-page/']:
  run('open',BASE+path);settle();r=run('a11y');result={'path':path,'width':w,'counts':r['counts'],'violations':r['violations'],'incomplete':r.get('incomplete',[])};results.append(result)
  print(json.dumps({'path':path,'width':w,'violations':[{'id':v['id'],'impact':v['impact'],'targets':[n['target'] for n in v['nodes']]} for v in r['violations']]}),flush=True)
 run('open',BASE+'/pattern-gallery/')
 if w==320:
  run('click','button[aria-label="Open menu"]');settle();r=run('a11y');results.append({'path':'menu open','width':w,'counts':r['counts'],'violations':r['violations'],'incomplete':r.get('incomplete',[])});run('press','Escape')
 run('click','.wl-search-pill button');settle();r=run('a11y');results.append({'path':'search open','width':w,'counts':r['counts'],'violations':r['violations'],'incomplete':r.get('incomplete',[])})
OUTPUT.write_text(json.dumps(results,indent=2)+'\n')
run('close')
