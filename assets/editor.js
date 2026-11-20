(() => {
    const el = wp.element.createElement;
    const { __, sprintf } = wp.i18n;
    const { SelectControl, Placeholder, Notice, Button } = wp.components;
    wp.blocks.registerBlockType('cr-vibecast/cast', {
        apiVersion: 3,
        title: __('Vibecast', 'cast-vibecoded-content'),
        description: __('Display published HTML from your Vibecast library.', 'cast-vibecoded-content'),
        icon: 'editor-code', category: 'widgets',
        attributes: { id: { type: 'integer', default: 0 }, height: { type: 'integer', default: 0 } },
        supports: { html: false },
        edit: ({ attributes, setAttributes }) => {
            const casts = window.vibecastLibrary?.casts || [];
            const missing = attributes.id > 0 && !casts.some(cast => cast.id === attributes.id);
            const options = [
                { label: __('Select a Vibecast', 'cast-vibecoded-content'), value: 0 },
                ...casts.map(cast => ({ label: `${cast.title || __('Untitled', 'cast-vibecoded-content')} (#${cast.id})`, value: cast.id })),
            ];
            if (missing) options.push({ label: sprintf(__('Unavailable Vibecast (#%d)', 'cast-vibecoded-content'), attributes.id), value: attributes.id });
            return el('div', wp.blockEditor.useBlockProps(),
                el(Placeholder, { label: __('Vibecast', 'cast-vibecoded-content'), icon: 'editor-code' },
                    el(SelectControl, {
                        label: __('Published Vibecast', 'cast-vibecoded-content'),
                        help: casts.length ? __('Uses the responsive heights saved with your Vibecast.', 'cast-vibecoded-content') : __('Publish a Vibecast, then reload this editor to select it.', 'cast-vibecoded-content'),
                        value: attributes.id, options,
                        onChange: value => setAttributes({ id: Number(value), height: 0 }),
                    }),
                    missing ? el(Notice, { status: 'warning', isDismissible: false }, __('This selection is unavailable. Check that the Vibecast is published, then reload this editor.', 'cast-vibecoded-content')) : null,
                    attributes.height > 0 ? el(Notice, { status: 'info', isDismissible: false },
                        __('This older placement has a fixed height.', 'cast-vibecoded-content'),
                        el(Button, { variant: 'secondary', onClick: () => setAttributes({ height: 0 }) }, __('Use saved responsive heights', 'cast-vibecoded-content'))
                    ) : null
                )
            );
        },
        save: () => null,
    });
})();
