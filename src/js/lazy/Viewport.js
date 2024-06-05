Ext.define('Tualo.mail.Viewport', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.tualo_mail',
    requires: [
        'Tualo.mail.models.Viewport',
        'Tualo.mail.controller.Viewport',

    ],
    controller: 'tualo_mail',
    viewModel: {
        type: 'tualo_mail'
    },
        /*
        'Tualo.Documentscanner.field.Input',
        'Tualo.Documentscanner.field.Camera'
        */
    layout: 'fit',
    bodyPadding: 10,
    listeners: {
        afterrender: function(){
            console.log('afterrender');
            this.getViewModel().getStore('data').load();
            
        }
    },
    items:[
        {
            xtype: 'panel',
            html: 'Mail'
            //xtype: 'tualo_documentscanner',
            
        }            
    ]
});

