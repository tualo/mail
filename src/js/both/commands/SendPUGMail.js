Ext.define('Tualo.cmp.mail.commands.SendPUGMail', {
    statics:{
      glyph: 'paper-plane',
      title: 'E-Mail senden',
      tooltip: 'E-Mail senden'
    },
    extend: 'Ext.panel.Panel',
    alias: 'widget.sendpugmail',
    layout: 'fit',
    items: [
      {
        xtype: 'form',
        itemId: 'mailform',
        bodyPadding: '25px',
        scrollable: 'y',
        disabled: true,
        items: [
            {
                xtype: 'textfield',
                name: 'mailfrom',
                label: 'Von',
            },
            {
                xtype: 'textfield',
                name: 'mailto',
                label: 'An'
            },
            {
                xtype: 'textfield',
                label: 'Betreff',
                name: 'mailsubject'
            },
            {
                xtype: 'textarea',
                name: 'mailbody'
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
