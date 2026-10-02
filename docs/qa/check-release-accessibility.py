"""Record axe checks on official data and persistent polish fixtures."""
import json
import os
from pathlib import Path
import subprocess

BROWSER = os.environ.get('AGENT_BROWSER', 'agent-browser')
BASE = os.environ.get('QA_URL', 'http://localhost:8089')
OUTPUT = Path(os.environ.get('QA_OUTPUT', 'docs/qa/release-1.4/accessibility.json'))
SESSION = 'wildlemon-release14'
PATHS = [
    '/', '/archive-regression/', '/pattern-gallery/',
    '/markup-html-tags-and-formatting/', '/markup-image-alignment/',
    '/template-comments/', '/template-comments/comment-page-1/',
    '/about/page-with-comments/', '/template-password-protected/',
    '/edge-case-no-title/', '/?s=markup', '/?s=zzzznothing',
    '/missing-local-page/', '/blocks-widgets/', '/edge-case-many-categories/',
    '/wl-polish-brief-title/', '/wl-polish-four-categories/',
    '/wl-polish-seven-categories/', '/author/wildlemonqa/',
    '/wl-polish-patterns-normal/', '/wl-polish-patterns-column-320/',
    '/wl-polish-patterns-column-600/',
]


def run(*args):
    response = subprocess.run(
        [BROWSER, '--session', SESSION, '--json', *args],
        capture_output=True, text=True, check=True,
    )
    result = json.loads(response.stdout)
    if not result['success']:
        raise RuntimeError(result)
    return result['data']


def settle():
    run('eval', 'Promise.all([document.fonts.ready,...document.getAnimations().map(a=>a.finished)]).then(()=>true)')


results = []


def scan(path, width):
    settle()
    data = run('a11y')
    record = {key: data[key] for key in ('axeVersion', 'counts', 'violations', 'incomplete')}
    record.update(path=path, width=width)
    results.append(record)
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(json.dumps(results, indent=2) + '\n')
    print(json.dumps({'path': path, 'width': width, 'violations': [v['id'] for v in data['violations']]}), flush=True)


try:
    for width in (320, 1440):
        run('set', 'viewport', str(width), '900')
        for path in PATHS:
            run('open', BASE + path)
            scan(path, width)
        run('open', BASE + '/pattern-gallery/')
        if width == 320:
            run('click', 'button[aria-label="Open menu"]')
            scan('mobile menu open', width)
        else:
            run('click', 'button[aria-label="Level 1 submenu"]')
            run('click', 'button[aria-label="Level 2 submenu"]')
            scan('nested desktop submenu open', width)
        run('open', BASE + '/pattern-gallery/')
        run('click', '.wl-search-pill button')
        scan('header search open', width)
finally:
    run('close')

# Preserve official edge-case content; fail on any finding beyond these exact fixtures.
unexpected = []
for result in results:
    for finding in result['violations']:
        empty_header = (
            result['path'] in ('/markup-html-tags-and-formatting/', '/template-comments/comment-page-1/')
            and finding['id'] == 'empty-table-header'
            and all(node['html'] == '<th></th>' for node in finding['nodes'])
        )
        untitled = result['path'] == '/edge-case-no-title/' and finding['id'] == 'page-has-heading-one'
        if not (empty_header or untitled):
            unexpected.append({'path': result['path'], 'width': result['width'], 'finding': finding})
if unexpected:
    raise SystemExit(json.dumps({'unexpected': unexpected}))
