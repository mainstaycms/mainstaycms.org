/*
 | What the page does once it runs: copy buttons, tabs, the plugins' search,
 | the docs' menu and the section a contents list marks as being read. Each
 | starts from a page that shows everything without it -- every tab's panel,
 | every plugin, the whole menu -- so a page whose script did not run, or the
 | admin's Content tab, which draws the page to edit it, misses nothing.
 */
const copiedFor = 2000;
const isEdited = document.querySelector('script[data-mainstay-content]') !== null;

/*
 | A Copy button, shown only where the page has a clipboard to write to,
 | which a page served over plain HTTP has not. It copies its data-text, or
 | the code in the box it is in, and says Copied for a moment.
 */
function startCopying() {
    if (!navigator.clipboard) {
        return;
    }

    for (const button of document.querySelectorAll('[data-copy]')) {
        button.hidden = false;
    }

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy]');

        if (!button) {
            return;
        }

        const text = button.dataset.text ?? button.closest('.cb, .install').querySelector('code').textContent;
        const label = button.innerHTML;

        await navigator.clipboard.writeText(text);

        button.textContent = button.dataset.copied;
        setTimeout(() => {
            button.innerHTML = label;
        }, copiedFor);
    });
}

/*
 | A row of tabs over panels, one shown at a time; the arrow keys, Home and
 | End move along the row as a tab list's should.
 */
function startTabs(container) {
    const list = container.querySelector('[role="tablist"]');
    const tabs = [...list.querySelectorAll('[role="tab"]')];
    const panelOf = (tab) => document.getElementById(tab.getAttribute('aria-controls'));

    const choose = (chosen) => {
        for (const tab of tabs) {
            const isChosen = tab === chosen;

            tab.setAttribute('aria-selected', String(isChosen));
            tab.tabIndex = isChosen ? 0 : -1;
            panelOf(tab).hidden = !isChosen;
        }

        panelOf(chosen).classList.add('swap');
    };

    for (const tab of tabs) {
        panelOf(tab).setAttribute('role', 'tabpanel');
        panelOf(tab).setAttribute('aria-labelledby', tab.id);
    }

    list.addEventListener('click', (event) => {
        const tab = event.target.closest('[role="tab"]');

        if (tab) {
            choose(tab);
        }
    });

    list.addEventListener('keydown', (event) => {
        const at = tabs.indexOf(document.activeElement);
        const to = {
            ArrowRight: at + 1,
            ArrowLeft: at - 1,
            Home: 0,
            End: tabs.length - 1,
        }[event.key];

        if (at === -1 || to === undefined) {
            return;
        }

        event.preventDefault();

        const tab = tabs[(to + tabs.length) % tabs.length];

        choose(tab);
        tab.focus();
    });

    list.hidden = false;
    container.dataset.tabbed = '';
    choose(tabs.find((tab) => tab.getAttribute('aria-selected') === 'true') ?? tabs[0]);
}

/*
 | The plugins' search and filter: a card is shown when its words hold what
 | is typed and it adds what is chosen.
 */
function startPluginSearch(container) {
    const input = container.querySelector('input[type="search"]');
    const filters = [...container.querySelectorAll('[data-filter]')];
    const cards = [...container.querySelectorAll('[data-adds]')];
    const empty = container.querySelector('.empty');
    let chosen = '';

    const show = () => {
        const query = input.value.trim().toLowerCase();
        let shown = 0;

        for (const card of cards) {
            const isMatch = card.dataset.words.includes(query) && (chosen === '' || JSON.parse(card.dataset.adds).includes(chosen));

            card.hidden = !isMatch;
            shown += isMatch ? 1 : 0;
        }

        empty.hidden = shown > 0;
    };

    container.addEventListener('click', (event) => {
        const filter = event.target.closest('[data-filter]');

        if (!filter) {
            return;
        }

        chosen = filter.dataset.filter;

        for (const other of filters) {
            other.setAttribute('aria-pressed', String(other === filter));
        }

        show();
    });

    input.addEventListener('input', show);
    container.querySelector('.toolbar').hidden = false;
}

/*
 | The docs' menu, folded on a narrow screen, where it would stand before the
 | doc's title, and open again whenever the screen is wide.
 */
function foldSideMenu(menu) {
    const wide = window.matchMedia('(min-width: 961px)');
    const fold = () => {
        menu.open = wide.matches;
    };

    wide.addEventListener('change', fold);
    fold();
}

/*
 | A contents list marks the section being read: the last one scrolled up to
 | where a jump from the list lands it, under the sticky header. Above the
 | first, none is. At the foot of the page, which the last sections cannot
 | scroll that far past, the one jumped to is marked while it is in sight, and
 | the last one otherwise.
 */
function followContents(contents) {
    const links = [...contents.querySelectorAll('a[href^="#"]')];
    const sectionOf = (hash) => document.getElementById(decodeURIComponent(hash.slice(1)));
    const sections = links.map((link) => sectionOf(link.hash));
    let isQueued = false;

    /* A pixel of give for a jump that lands a fraction short. */
    const hasLanded = (section) =>
        section.getBoundingClientRect().top <= parseFloat(getComputedStyle(section).scrollMarginTop) + 1;
    const isInSight = (section) => {
        const { top } = section.getBoundingClientRect();

        return top >= 0 && top < window.innerHeight;
    };
    const isAtFoot = () => window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 1;

    const reachedIndex = () => {
        if (!isAtFoot()) {
            return sections.findLastIndex(hasLanded);
        }

        const jumpedTo = links.findIndex((link) => link.hash === location.hash);

        return jumpedTo !== -1 && isInSight(sections[jumpedTo]) ? jumpedTo : sections.length - 1;
    };

    const mark = () => {
        const reached = reachedIndex();

        links.forEach((link, index) => {
            if (index === reached) {
                link.setAttribute('aria-current', 'true');
            } else {
                link.removeAttribute('aria-current');
            }
        });

        isQueued = false;
    };

    const queue = () => {
        if (!isQueued) {
            isQueued = true;
            requestAnimationFrame(mark);
        }
    };

    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', queue);
    /* A jump between sections at the foot scrolls nothing. */
    window.addEventListener('hashchange', queue);
    mark();
}

startCopying();

if (!isEdited) {
    document.querySelectorAll('[data-tabs]').forEach(startTabs);
    document.querySelectorAll('[data-plugins]').forEach(startPluginSearch);
    document.querySelectorAll('[data-side-menu]').forEach(foldSideMenu);
    document.querySelectorAll('[data-contents]').forEach(followContents);
}
