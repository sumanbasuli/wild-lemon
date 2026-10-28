"""Validate theme sources in a signed-in real block editor and save a QA draft."""
import hashlib
import json
import os
from pathlib import Path
import subprocess

BROWSER = os.environ.get('AGENT_BROWSER', 'agent-browser')
SESSION = os.environ.get('QA_SESSION', 'wildlemon-editor-oct28')
OUTPUT = Path(os.environ.get('QA_OUTPUT', 'docs/qa/review-2026-10-28/editor-current.json'))
PATTERNS = Path(os.environ.get('QA_PATTERNS', 'docs/qa/review-2026-10-28/patterns-current.json'))
inputs = [{'name': str(p), 'content': p.read_text()} for directory in ('templates', 'parts') for p in Path(directory).glob('*.html')]
patterns = json.loads(PATTERNS.read_text())
inputs.extend(patterns)
public = {'wild-lemon/editorial-introduction', 'wild-lemon/seasonal-table', 'wild-lemon/photo-essay', 'wild-lemon/two-column-notes', 'wild-lemon/slow-weekend', 'wild-lemon/featured-quotation'}
content = '\n'.join(p['content'] for p in patterns if p['name'] in public)
assert sum(p['name'] in public for p in patterns) == 6


def evaluate(source):
    process = subprocess.run([BROWSER, '--session', SESSION, '--json', 'eval', '--stdin'], input=source, capture_output=True, text=True, check=True)
    response = json.loads(process.stdout)
    if not response['success']:
        raise RuntimeError(response)
    return response['data']['result']


source = '''(async()=>{
 const parseOnly=PARSE_ONLY;
 const existing=wp.data.select('core/editor');
 if(!parseOnly&&(existing.getCurrentPostType()!=='post'||!(existing.isEditedPostNew()||existing.getCurrentPostAttribute('slug')==='wl-review-editor-roundtrip')))throw new Error('Open a new QA post or its marked round-trip draft first.');
 const sources=INPUTS,content=CONTENT;
 const invalid=blocks=>blocks.flatMap(b=>[...(b.isValid===false?[b.name]:[]),...invalid(b.innerBlocks||[])]);
 const sourcesResult=sources.map(s=>{const parsed=wp.blocks.parse(s.content);const serialized=wp.blocks.serialize(parsed);return {name:s.name,blockCount:parsed.length,invalid:invalid(parsed),roundTripInvalid:invalid(wp.blocks.parse(serialized))};});
 if(sourcesResult.some(r=>r.invalid.length||r.roundTripInvalid.length))return {sources:sourcesResult,saved:false};
 if(parseOnly)return {sources:sourcesResult,parseOnly:true,saved:false};
 const blocks=wp.blocks.parse(content);
 wp.data.dispatch('core/block-editor').resetBlocks(blocks);
 wp.data.dispatch('core/editor').editPost({title:'Wild Lemon QA editor round trip',slug:'wl-review-editor-roundtrip',status:'draft'});
 await wp.data.dispatch('core/editor').savePost();
 const editor=wp.data.select('core/editor');
 return {sources:sourcesResult,saved:true,id:editor.getCurrentPostId(),status:editor.getCurrentPostAttribute('status'),serialized:editor.getEditedPostContent(),invalid:invalid(wp.data.select('core/block-editor').getBlocks()),postContentSpacing:wp.blocks.getBlockType('core/post-content').supports.spacing};
})()'''.replace('INPUTS', json.dumps(inputs)).replace('CONTENT', json.dumps(content)).replace('PARSE_ONLY', json.dumps(os.environ.get('QA_PARSE_ONLY') == '1'))
result = evaluate(source)
if result.get('parseOnly'):
    OUTPUT.write_text(json.dumps(result, indent=2) + '\n')
    print(json.dumps({'sources': len(result['sources']), 'invalid': 0, 'parseOnly': True}))
    raise SystemExit(0)
if not result.get('saved'):
    OUTPUT.write_text(json.dumps(result, indent=2) + '\n')
    raise SystemExit('Editor reported invalid blocks; draft not saved.')
assert result['status'] == 'draft' and not result['invalid'], result
expected = result.pop('serialized')
result['serializedSha256'] = hashlib.sha256(expected.encode()).hexdigest()
edit_url = evaluate('location.origin') + '/wp-admin/post.php?post=' + str(result['id']) + '&action=edit'
for command in [('open', edit_url), ('wait', '--load', 'networkidle')]:
    subprocess.run([BROWSER, '--session', SESSION, '--json', *command], capture_output=True, text=True, check=True)
reloaded = evaluate('''(async()=>{const e=wp.data.select('core/editor');const content=e.getEditedPostContent();const bytes=await crypto.subtle.digest('SHA-256',new TextEncoder().encode(content));const invalid=bs=>bs.flatMap(b=>[...(b.isValid===false?[b.name]:[]),...invalid(b.innerBlocks||[])]);return {id:e.getCurrentPostId(),status:e.getCurrentPostAttribute('status'),serializedSha256:[...new Uint8Array(bytes)].map(v=>v.toString(16).padStart(2,'0')).join(''),invalid:invalid(wp.data.select('core/block-editor').getBlocks()),blockCount:wp.data.select('core/block-editor').getBlocks().length}})()''')
assert reloaded['id'] == result['id'] and reloaded['status'] == 'draft' and not reloaded['invalid'], reloaded
assert reloaded['serializedSha256'] == result['serializedSha256'], reloaded
result['reloaded'] = reloaded
OUTPUT.write_text(json.dumps(result, indent=2) + '\n')
print(json.dumps({'sources': len(result['sources']), 'invalid': 0, 'draft': result['id'], 'status': result['status'], 'serializedSha256': result['serializedSha256']}))
