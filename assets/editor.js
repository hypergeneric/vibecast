(() => {
    const el = wp.element.createElement;
    const { __, sprintf } = wp.i18n;
    const { SelectControl, Placeholder, Notice, Button } = wp.components;
    wp.blocks.registerBlockType('cr-vibecast/cast', {
        apiVersion: 3,
        title: __('Vibecast', 'vibecast'),
        description: __('Display published HTML from your Vibecast library.', 'vibecast'),
        icon: 'editor-code', category: 'widgets',
        attributes: { id: { type: 'integer', default: 0 }, height: { type: 'integer', default: 0 } },
        supports: { html: false },
        edit: ({ attributes, setAttributes }) => {
            const casts = window.vibecastLibrary?.casts || [];
            const missing = attributes.id > 0 && !casts.some(cast => cast.id === attributes.id);
            const options = [
                { label: __('Select a Vibecast', 'vibecast'), value: 0 },
                ...casts.map(cast => ({ label: `${cast.title || __('Untitled', 'vibecast')} (#${cast.id})`, value: cast.id })),
            ];
            if (missing) options.push({ label: sprintf(__('Unavailable Vibecast (#%d)', 'vibecast'), attributes.id), value: attributes.id });
            return el('div', wp.blockEditor.useBlockProps(),
                el(Placeholder, { label: __('Vibecast', 'vibecast'), icon: 'editor-code' },
                    el(SelectControl, {
                        label: __('Published Vibecast', 'vibecast'),
                        help: casts.length ? __('Uses the responsive heights saved with your Vibecast.', 'vibecast') : __('Publish a Vibecast, then reload this editor to select it.', 'vibecast'),
                        value: attributes.id, options,
                        onChange: value => setAttributes({ id: Number(value), height: 0 }),
                    }),
                    missing ? el(Notice, { status: 'warning', isDismissible: false }, __('This selection is unavailable. Check that the Vibecast is published, then reload this editor.', 'vibecast')) : null,
                    attributes.height > 0 ? el(Notice, { status: 'info', isDismissible: false },
                        __('This older placement has a fixed height.', 'vibecast'),
                        el(Button, { variant: 'secondary', onClick: () => setAttributes({ height: 0 }) }, __('Use saved responsive heights', 'vibecast'))
                    ) : null
                )
            );
        },
        save: () => null,
    });
})();
