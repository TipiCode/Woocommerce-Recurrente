const settings = window.wc.wcSettings.getSetting( 'recurrente_data', {} );
const label = window.wp.htmlEntities.decodeEntities( settings.title ) || window.wp.i18n.__( 'Recurrente', 'recurrente' );
const Content = () => {
    let desc =  window.wp.htmlEntities.decodeEntities( settings.description || '' );
    return desc;
};
const Icon = () => {
    let icon =  window.wp.htmlEntities.decodeEntities( settings.icon || '' );
    return icon;
};
const Block_Gateway = {
    name: 'recurrente',
    label: label,
    content: Object( window.wp.element.createElement )( Content, null ),
    edit: Object( window.wp.element.createElement )( Content, null ),
    canMakePayment: () => true,
    ariaLabel: label,
    placeOrderButtonLabel: "Proceder a recurrente",
    supports: {
        features: settings.supports,
    },
};
window.wc.wcBlocksRegistry.registerPaymentMethod( Block_Gateway );