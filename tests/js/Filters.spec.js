import { describe, it, expect, vi } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import { nextTick } from 'vue';
import Filters from '../../resources/js/components/Filtering/Filters.vue';
import SingleSelectFilter from '../../resources/js/components/Filtering/SingleSelectFilter.vue';
import MultiSelectFilter from '../../resources/js/components/Filtering/MultiSelectFilter.vue';
import filters from './fixtures/filters.js';

// The dropdowns stay as stubs that keep their props, so a test reads what each one is told to show
function mountBar(props = {}) {
    return shallowMount(Filters, {
        props: {
            filters,
            onFilter: vi.fn(),
            timeframetypeinput: 'minor',
            timeframeinput: ['2.57.0.98746'],
            gametypeinput: ['sl'],
            // jsdom has no layout, so the full bar's row sizing would compute NaN widths
            compact: true,
            ...props,
        },
        global: {
            components: { SingleSelectFilter, MultiSelectFilter },
        },
    });
}

// What the page gets when Filter is pressed
async function pressFilter(wrapper) {
    const button = wrapper.findAll('button').find(b => b.text().trim() === 'Filter');
    expect(button, 'the Filter button').toBeTruthy();
    await button.trigger('click');
    const calls = wrapper.props('onFilter').mock.calls;
    expect(calls.length, 'onFilter was called').toBeGreaterThan(0);
    return calls.at(-1)[0];
}

function dropdown(wrapper, text) {
    return wrapper.findAllComponents(SingleSelectFilter).find(c => c.props('text') === text);
}

describe('Filters.vue', () => {
    it('sends the hero, role, stat and party it opened with from the URL', async () => {
        const wrapper = mountBar({
            includehero: true,
            includerole: true,
            includestatfilter: true,
            includeteamoneparty: true,
            includeteamtwoparty: true,
            // The party filters live in the advanced row, which the Party page opens with
            advancedfiltering: true,
            heroinput: 1,
            roleinput: 'Tank',
            statfilterinput: 'kills',
            teamonepartyinput: '00005',
            teamtwopartyinput: '11111',
        });

        const sent = await pressFilter(wrapper);

        expect(sent.single['Heroes']).toBe(1);
        expect(sent.single['Role']).toBe('Tank');
        expect(sent.single['Stat Filter']).toBe('kills');
        expect(sent.single['Team One Party']).toBe('00005');
        expect(sent.single['Team Two Party']).toBe('11111');
    });

    it('shows the hero and role it sends', async () => {
        const wrapper = mountBar({ includehero: true, includerole: true, heroinput: 81, roleinput: 'Support' });

        const sent = await pressFilter(wrapper);

        expect(dropdown(wrapper, 'Heroes').props('defaultValue')).toBe(sent.single['Heroes']);
        expect(dropdown(wrapper, 'Role').props('defaultValue')).toBe(sent.single['Role']);
    });

    it('sends the first stat when the URL names none', async () => {
        const sent = await pressFilter(mountBar({ includestatfilter: true }));

        expect(sent.single['Stat Filter']).toBe('win_rate');
    });

    it('shows the hero it queries once Type is Hero', async () => {
        const wrapper = mountBar({ playerheroroletype: true, includehero: true });

        dropdown(wrapper, 'Type').vm.$emit('input-changed', { field: 'Type', value: 'Hero', type: 'single' });
        await nextTick();

        const sent = await pressFilter(wrapper);
        const heroes = dropdown(wrapper, 'Heroes');

        expect(heroes, 'the Heroes dropdown appears').toBeTruthy();
        expect(sent.single['Heroes']).toBeTruthy();
        expect(heroes.props('defaultValue')).toBe(sent.single['Heroes']);
    });

    it('sends no hero for the Player leaderboard, whatever the URL says', async () => {
        const sent = await pressFilter(mountBar({
            leaderboardfiltertype: true,
            leaderboardtypeinput: 'Player',
            includehero: true,
            heroinput: 1,
        }));

        expect(sent.single['Leaderboard Type']).toBe('Player');
        expect(sent.single['Heroes']).toBeUndefined();
    });
});
