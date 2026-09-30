import json,subprocess,pathlib
import os
B=os.environ.get('AGENT_BROWSER', 'agent-browser')
inputs=[{'name':str(p),'content':p.read_text()} for d in ['templates','parts'] for p in pathlib.Path(d).glob('*.html')]
js='''(()=>{const inputs=INPUTS;function check(bs,errors){for(const b of bs){if(!b.isValid)errors.push(b.name);check(b.innerBlocks||[],errors)}}return inputs.map(p=>{const errors=[];check(wp.blocks.parse(p.content),errors);return {name:p.name,errors}})})()'''.replace('INPUTS',json.dumps(inputs))
p=subprocess.run([B,'--session','wildlemon-editor','--json','eval','--stdin'],input=js,text=True,capture_output=True,check=True)
data=json.loads(p.stdout)['data']['result'];pathlib.Path('docs/qa/template-validation.json').write_text(json.dumps(data,indent=2));print(json.dumps(data))
