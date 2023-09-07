Ext.define('Tualo.cmp.mail.commands.SendPUGMail', {
    statics:{
      glyph: 'paper-plane',
      title: 'E-Mail senden',
      tooltip: 'E-Mail senden'
    },
    extend: 'Ext.panel.Panel',
    alias: 'widget.sendpugmail',
    layout: 'fit',
    viewModel: {
        data: {
            messagetitle: '',
            messagetext: '',
        },
        stores: {
            attachments: {
                type: 'array',
                data: [],
                fields: [
                    {name: 'filename', type: 'string'},
                    {name: 'title', type: 'string'},
                    {name: 'size', type: 'string'},
                    {name: 'contenttype', type: 'string'},
                ]
            }
        }
    },
    items: [
      {
        xtype: 'form',
        itemId: 'mailform',
        bodyPadding: '25px',
        scrollable: 'y',
        defaults: {
            labelWidth: 150,
            anchor: '100%'
        },
        disabled: true,
        items: [
            {
                xtype: 'textfield',
                name: 'mailfrom',
                fieldLabel: 'Von',
            },
            {
                xtype: 'textfield',
                name: 'mailto',
                fieldLabel: 'An'
            },
            {
                xtype: 'textfield',
                fieldLabel: 'Betreff',
                name: 'mailsubject'
            },
            {
                fieldLabel: 'Text',
                xtype: 'htmleditor',
                name: 'mailbody'
            },
            {
                xtype: 'tagfield',
                fieldLabel: 'Anhänge',
                bind: {
                    store: '{attachments}',
                },
                displayField: 'title',
                valueField: 'filename',
                queryMode: 'local',
                filterPickList: true
            }
        ]
      },{
        hidden: true,
        xtype: 'panel',
        itemId: 'messagepanel',
        layout:{
          type: 'vbox',
          align: 'center'
        },
        items: [
          {
            xtype: 'component',
            style:{
                backrgoundColor: '#8acdeb'
            },
            cls: 'lds-container',
            html: '<div id="container">'+
            '<div class="steam" id="steam1"> </div>'+
            '<div class="steam" id="steam2"> </div>'+
            '<div class="steam" id="steam3"> </div>'+
            '<div class="steam" id="steam4"> </div>'+
            '<div id="cup">'+
              '<div id="cup-body">'+
              '<div id="cup-shade"></div>'+
                '</div>'+
              '<div id="cup-handle"></div>'+
              '</div>'+
            '<div id="saucer"></div>'+
            '<div id="shadow"></div>'+
            '</div>'+
            '<div><h3>{messagetitle}</h3>'+
            '<span>{messagetext}</span></div>'
          }
        ]
      },{
        hidden: true,
        xtype: 'panel',
        itemId: 'waitpanel',
        layout:{
          type: 'vbox',
          align: 'center'
        },
        items: [
          {
            xtype: 'component',
            cls: 'lds-container',
            html: '<div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
            +'<div><h3>Die Mail wird gesendet</h3>'
            +'<span>Einen Moment bitte ...</span></div>'
          }
        ]
      }
    ],
    loadRecord: function(record,records,selectedrecords){
      this.record = record;
      this.records = records;
      this.selectedrecords = selectedrecords;
      if(typeof this.record.get('__sendmail_info')=='undefined'){
        me.getComponent('syncform').hide();
        me.getComponent('waitpanel').hide();
        me.getComponent('messagepanel').show();
        return;
      }
      this.fillform();
    },
    fillform: async function(){
        let res = await fetch('./mail/renderpug',{
            method: 'PUT',
            body: JSON.stringify(this.record.getData())
        });
        res = await res.json();
        if (res.success){
            this.getViewModel().getStore('attachments').setData(res.attachments);

            this.getComponent('mailform').getForm().setValues(res.data);
            this.getComponent('mailform').enable();
            this.getComponent('waitpanel').hide();
        }

    },
    getNextText: function(){
      return 'Senden';
    },
    run: async function(){
      let me = this;
      me.getComponent('syncform').hide();
      me.getComponent('waitpanel').show();
      let res= await Tualo.Fetch.post('./mail/send/renderpug',{      });
      if (res.success !== true){
        Ext.toast({
            html: res.msg,
            title: 'Fehler',
            align: 't',
            iconCls: 'fa fa-warning'
        });
      }
      return res;
    }
  });
