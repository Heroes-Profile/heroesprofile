import { describe, it, expect } from 'vitest';
import fs from 'fs';
import path from 'path';

// app.js registers components by file name, and unplugin-vue-components resolves template tags the same way
const componentsDir = 'resources/js/components';
const viewsDir = 'resources/views';

const kebab = name => name.replace(/([a-z0-9])([A-Z])/g, '$1-$2').replace(/([A-Z])([A-Z][a-z])/g, '$1-$2').toLowerCase();

function filesIn(dir, extension) {
    return fs.readdirSync(dir, { recursive: true })
        .map(file => file.split(path.sep).join('/'))
        .filter(file => file.endsWith(extension));
}

const componentFiles = filesIn(componentsDir, '.vue');
const componentsByTag = new Map(componentFiles.map(file => [kebab(path.basename(file, '.vue')), file]));

// Hyphenated tags in a Blade view; x-* are Blade's own components
function vueTags(view) {
    const source = fs.readFileSync(path.join(viewsDir, view), 'utf8');
    const tags = [...source.matchAll(/<([a-z][a-z0-9]*(?:-[a-z0-9]+)+)[\s>/]/g)].map(match => match[1]);
    return [...new Set(tags)].filter(tag => !tag.startsWith('x-'));
}

// Rendered on every page, so they load with app.js instead of appearing late
const layoutViews = filesIn(viewsDir, '.blade.php')
    .filter(view => /^(layouts\/|errors\/|api\/partials\/|nav\.blade\.php$)/.test(view));

describe('Vue components', () => {
    it('have unique file names', () => {
        const seen = new Map();
        const clashes = [];
        for (const file of componentFiles) {
            const name = path.basename(file, '.vue');
            if (seen.has(name)) {
                clashes.push(`${seen.get(name)} and ${file}`);
            }
            seen.set(name, file);
        }
        expect(clashes, 'two components with the same name; only one gets registered').toEqual([]);
    });

    it('exist for every tag a Blade view mounts', () => {
        const missing = filesIn(viewsDir, '.blade.php').flatMap(view =>
            vueTags(view).filter(tag => !componentsByTag.has(tag)).map(tag => `<${tag}> in ${view}`));
        expect(missing, 'no component with that name; the tag renders nothing').toEqual([]);
    });

    it('that the layouts mount load with app.js', () => {
        const appJs = fs.readFileSync('resources/js/app.js', 'utf8');
        const eagerGlob = appJs.match(/import\.meta\.glob\(\[([\s\S]*?)\],\s*\{\s*eager:\s*true/);
        expect(eagerGlob, 'the eager component glob in app.js').toBeTruthy();
        const eager = new Set([...eagerGlob[1].matchAll(/'\.\/components\/([^']+\.vue)'/g)].map(match => match[1]));

        const missing = layoutViews.flatMap(view =>
            vueTags(view).filter(tag => componentsByTag.has(tag) && !eager.has(componentsByTag.get(tag)))
                .map(tag => `<${tag}> in ${view}: add './components/${componentsByTag.get(tag)}' to app.js`));
        expect(missing, 'mounted by a layout but loaded async').toEqual([]);
    });
});
