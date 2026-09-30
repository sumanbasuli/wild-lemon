"""Audit every published Theme Unit Test post/page, plus theme template routes.

Use a dedicated WordPress installation with the official fixtures and export its
post inventory (see official-2026-09-30/README.md). Does not change site content.
AGENT_BROWSER, QA_URL, QA_SESSION and QA_WIDTHS can override the local defaults.
"""
import json
import os
import pathlib
import subprocess
import sys
from urllib.parse import urlsplit

browser = os.environ.get('AGENT_BROWSER', 'agent-browser')
base = os.environ.get('QA_URL', 'http://localhost:8089').rstrip('/')
session = os.environ.get('QA_SESSION', 'wildlemon-official')
widths = [int(w) for w in os.environ.get('QA_WIDTHS', '320,390,768,1024,1440').split(',')]
inventory = json.loads(pathlib.Path(sys.argv[1]).read_text())
output = pathlib.Path(sys.argv[2])


def run(*args):
    p = subprocess.run([browser, '--session', session, '--json', *args], capture_output=True, text=True)
    if p.returncode:
        raise RuntimeError(p.stdout + p.stderr)
    return json.loads(p.stdout)['data']


paths = list(dict.fromkeys(urlsplit(p['url']).path for p in inventory if p['status'] == 'publish'))
paths += ['/', '/?query-11-page=2', '/pattern-gallery/', '/archive-regression/',
          '/category/markup/', '/tag/8bit/', '/author/themereviewteam/',
          '/?s=markup', '/?s=zzzznothing', '/missing-local-page/']
paths = list(dict.fromkeys(paths))
measure = r'''(async () => {
    await document.fonts.ready;
    const vw = document.documentElement.clientWidth;
    const rect = e => e.getBoundingClientRect();
    const visible = e => rect(e).width > 0 && rect(e).height > 0 && getComputedStyle(e).visibility !== 'hidden';
    const titleIssues = [...document.querySelectorAll('.wl-row-link .wp-block-post-title, .wl-media-row .wp-block-post-title, .wl-hero .wp-block-post-title')]
        .filter(e => visible(e) && e.textContent.trim().length > 15 && rect(e).width < Math.min(160, vw * .5))
        .map(e => ({text: e.textContent, width: rect(e).width, height: rect(e).height}));
    const offenders = [...document.querySelectorAll('main *,header *,footer *')].filter(e => {
        const r = rect(e), s = getComputedStyle(e);
        return visible(e) && (r.right > vw + 1 || r.left < -1) &&
            !['absolute', 'fixed'].includes(s.position) &&
            !e.closest('.wp-block-navigation__responsive-container:not(.is-menu-open)') &&
            !e.closest('table, pre');
    }).slice(0, 8).map(e => ({tag: e.tagName, cls: String(e.className).slice(0,100), width: rect(e).width}));
    return {
        overflow: document.documentElement.scrollWidth - vw,
        titleIssues, offenders,
        main: document.querySelectorAll('main').length,
        headings: [...document.querySelectorAll('main h1')].map(e => e.textContent),
        comments: document.querySelectorAll('.wp-block-comments').length,
        missingImageAlt: [...document.querySelectorAll('main img')].filter(e => !e.hasAttribute('alt')).map(e=>e.src),
        brokenLoadedLocalImages: [...document.querySelectorAll('main img')].filter(e => e.src.startsWith(location.origin) && e.complete && !e.naturalWidth).map(e => e.src),
        passwordForm: !!document.querySelector('.post-password-form')
    };
})()'''
results = []
try:
    for width in widths:
        run('set', 'viewport', str(width), '900')
        for path in paths:
            run('open', base + path)
            result = run('eval', measure)['result']
            result.update(path=path, width=width)
            results.append(result)
            if result['overflow'] > 1 or result['titleIssues'] or result['brokenLoadedLocalImages']:
                print(json.dumps(result), flush=True)
        print(f'Completed {len(paths)} routes at {width}px', flush=True)
finally:
    output.write_text(json.dumps(results, indent=2) + '\n')
    run('close')
print(json.dumps({'cases': len(results), 'overflow': sum(r['overflow'] > 1 for r in results),
                  'compressedTitles': sum(bool(r['titleIssues']) for r in results),
                  'brokenLocalImages': sum(bool(r['brokenLoadedLocalImages']) for r in results)}))
