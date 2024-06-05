Ext.define('Tualo.routes.Mail', {
    statics: {
        
        load: async function () {
            let list = [];
            /*
            let response = await Tualo.Fetch.post('ds/ds/read', { limit: 10000 });
            if (response.success == true) {
                for (let i = 0; i < response.data.length; i++) {
                    if (!Ext.isEmpty(response.data[i].table_name))
                        list.push({
                            name: response.data[i].title + ' (' + '#ds/' + response.data[i].table_name + ')',
                            path: '#ds/' + response.data[i].table_name
                        });
                }
            }*/
            return list;
        },

        sha1: async function (str) {
            const enc = new TextEncoder();
            if (crypto.subtle){
                const hash = await crypto.subtle.digest('SHA-1', enc.encode(str));
                return Array.from(new Uint8Array(hash))
                    .map(v => v.toString(16).padStart(2, '0'))
                    .join('');
            }else{
                return btoa(str).replace(/[^A-Za-z0-9]/,'');
            }
        }
    },
    url: 'mail/:{id}',
    handler: {
        action: function (values) {

            console.log('mail route', 'action', values);
            Ext.getApplication().addView('Tualo.mail.Viewport', {
                mailId:  values.id
            });
            
            /*
            const fnx = async () => {
                let type = 'dsview',
                    tablename = values.table,
                    mainView = Ext.getApplication().getMainView(),
                    tablenamecase = tablename.toLocaleUpperCase().substring(0, 1) + tablename.toLowerCase().slice(1),
                    stage = mainView.getComponent('dashboard_dashboard').getComponent('stage'),
                    component = null,
                    cmp_id = type + '_' + tablename.toLowerCase() + '_' + (await Tualo.routes.DS.sha1(JSON.stringify(values)));
                    console.log('ds route','action','cmp_id', cmp_id);
                component = stage.getComponent(cmp_id);

                if (!Ext.isEmpty(component)) {
                    stage.setActiveItem(component);
                    console.log('ds route','action','setActiveItem', component);
                } else {
                    component = Ext.getApplication().addView('Tualo.D3Charts.' + type + '.' + tablenamecase, {
                        itemId: cmp_id
                    });
                    console.log('ds route','action','new', component);
                }

                if ((component) && (typeof values.fieldValue != 'undefined')) {
                    component.filterField(values.fieldName, values.fieldValue);
                }
            }
            fnx();
            */
        },
        before: function (values, action) {
            action.resume();
        }
    }
});
