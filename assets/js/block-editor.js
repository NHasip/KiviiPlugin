/**
 * Kivii Online Afspraak – Gutenberg Block Editor
 */
(function (blocks, element, components) {
    const el = element.createElement;
    const { registerBlockType } = blocks;
    const { Placeholder } = components;

    registerBlockType('kiviiweb/booking-form', {
        title: 'Kivii Boekingsformulier',
        description: 'Plaats het online afspraak boekingsformulier.',
        icon: 'calendar-alt',
        category: 'widgets',
        keywords: ['kivii', 'afspraak', 'booking', 'agenda'],
        edit: function () {
            return el(
                Placeholder,
                {
                    icon: 'calendar-alt',
                    label: 'Kivii Boekingsformulier',
                    instructions: 'Het boekingsformulier wordt hier getoond op de pagina. U kunt het formulier configureren via het Kivii Afspraak menu in het admin panel.',
                }
            );
        },
        save: function () {
            return null; // Server-side render
        },
    });
})(window.wp.blocks, window.wp.element, window.wp.components);
