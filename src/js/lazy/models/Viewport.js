Ext.define('Tualo.mail.models.Viewport', {
    extend: 'Ext.app.ViewModel',
    alias: 'viewmodel.tualo_mail',
    data:{
        currentWMState: 'unkown'
    },
    stores: {
        data: {
            type: 'json',
            proxy: {
                timeout: 600000,
                type: 'ajax',
                reader: 'json',
                url: './mail/list'
            },
        
            timeout: 600000,

        }
    }
});