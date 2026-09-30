import json,subprocess
import os
B=os.environ.get('AGENT_BROWSER', 'agent-browser')
subprocess.run([B,'--session','wildlemon-a11y','set','viewport','1280','900'],check=True,capture_output=True)
results=[]
for path in ['/','/pattern-gallery/','/markup-html-tags-and-formatting/','/template-comments/','/?s=zzzznothing','/missing-local-page/']:
 subprocess.run([B,'--session','wildlemon-a11y','open','http://localhost:8088'+path],check=True,capture_output=True)
 p=subprocess.run([B,'--session','wildlemon-a11y','a11y','--json'],check=True,capture_output=True,text=True)
 d=json.loads(p.stdout)['data'];r={'path':path,'counts':d['counts'],'violations':d['violations']};results.append(r)
 print(path,json.dumps({'counts':d['counts'],'violations':[{'id':v['id'],'impact':v['impact'],'targets':[n['target'] for n in v['nodes']]} for v in d['violations']]}),flush=True)
open('docs/qa/accessibility-checks.json','w').write(json.dumps(results,indent=2))
