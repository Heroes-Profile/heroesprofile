import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import SingleSelectFilter from '../../resources/js/components/Filtering/SingleSelectFilter.vue';
import MultiSelectFilter from '../../resources/js/components/Filtering/MultiSelectFilter.vue';

const values = [
    { code: 'cursed', name: 'Cursed Hollow' },
    { code: 'dragon', name: 'Dragon Shire' },
    { code: 'towers', name: 'Towers of Doom' },
];

function lastEmitted(wrapper) {
    return wrapper.emitted('input-changed')?.at(-1)?.[0];
}

describe('SingleSelectFilter.vue', () => {
    it('shows its default and says what it holds', () => {
        const wrapper = mount(SingleSelectFilter, { props: { text: 'Map', values, defaultValue: 'dragon', placeholder: 'All' } });

        expect(wrapper.text()).toContain('Dragon Shire');
        expect(wrapper.attributes('data-filter')).toBe('Map');
        expect(wrapper.attributes('data-value')).toBe('dragon');
    });

    it('picks an option, and picking it again clears it back to All', async () => {
        const wrapper = mount(SingleSelectFilter, { props: { text: 'Map', values, placeholder: 'All' } });

        await wrapper.find('[tabindex="0"]').trigger('click');
        await wrapper.find('input[value="towers"]').trigger('click');
        expect(lastEmitted(wrapper)).toEqual({ field: 'Map', value: 'towers', type: 'single' });

        await wrapper.find('input[value="towers"]').trigger('click');
        expect(lastEmitted(wrapper).value).toBe('');
        expect(wrapper.text()).toContain('All');
    });

    it('narrows its options to the search', async () => {
        const wrapper = mount(SingleSelectFilter, { props: { text: 'Map', values } });

        await wrapper.find('[tabindex="0"]').trigger('click');
        await wrapper.find('input[type="text"]').setValue('drag');

        const shown = wrapper.findAll('input[type="radio"]').map(input => input.attributes('value'));
        expect(shown).toEqual(['dragon']);
    });
});

describe('MultiSelectFilter.vue', () => {
    it('shows its defaults and says what it holds', () => {
        const wrapper = mount(MultiSelectFilter, { props: { text: 'Regions', values, defaultValue: ['cursed', 'towers'] } });

        expect(wrapper.attributes('data-filter')).toBe('Regions');
        expect(JSON.parse(wrapper.attributes('data-value'))).toEqual(['cursed', 'towers']);
        expect(wrapper.text()).toContain('Cursed Hollow');
    });
});
