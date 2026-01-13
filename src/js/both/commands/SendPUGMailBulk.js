Ext.define('Tualo.cmp.mail.commands.SendPUGMailBulk', {
  statics: {
    glyph: 'envelopes-bulk',
    title: 'E-Mails senden',
    tooltip: 'E-Mails senden'
  },
  extend: 'Ext.panel.Panel',
  alias: 'widget.sendpugmailbulk',
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
          { name: 'filename', type: 'string' },
          { name: 'title', type: 'string' },
          { name: 'size', type: 'string' },
          { name: 'contenttype', type: 'string' },
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
      layout: {
        type: 'vbox',
        align: 'stretch'
      },
      disabled: true,
      items: [
        /*{
          xtype: 'textfield',
          name: 'mailfrom',
          itemId: 'mailfrom',
          fieldLabel: 'Von',
        },*/
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
          flex: 1,
          xtype: 'htmleditor',
          name: 'mailbody'
        },
        {
          xtype: 'tagfield',
          fieldLabel: 'Anhänge',
          itemId: 'attachments',
          name: 'attachments',
          bind: {
            store: '{attachments}',
          },
          displayField: 'title',
          valueField: 'filename',
          queryMode: 'local',
          filterPickList: true
        }
      ]
    }, {
      hidden: true,
      xtype: 'panel',
      itemId: 'messagepanel',
      layout: {
        type: 'vbox',
        align: 'center'
      },
      items: [
        {
          xtype: 'component',
          style: {
            backrgoundColor: '#8acdeb'
          },
          cls: 'lds-container',
          html: '<div id="container">' +
            '<div class="steam" id="steam1"> </div>' +
            '<div class="steam" id="steam2"> </div>' +
            '<div class="steam" id="steam3"> </div>' +
            '<div class="steam" id="steam4"> </div>' +
            '<div id="cup">' +
            '<div id="cup-body">' +
            '<div id="cup-shade"></div>' +
            '</div>' +
            '<div id="cup-handle"></div>' +
            '</div>' +
            '<div id="saucer"></div>' +
            '<div id="shadow"></div>' +
            '</div>' +
            '<div><h3>{messagetitle}</h3>' +
            '<span>{messagetext}</span></div>'
        }
      ]
    }, {
      hidden: true,
      xtype: 'panel',
      itemId: 'waitpanel',
      layout: {
        type: 'vbox',
        align: 'center'
      },
      items: [
        {
          xtype: 'component',
          cls: 'lds-container',
          html: '<div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
            + '<div><h3>Die Mail wird gesendet</h3>'
            + '<span>Einen Moment bitte ...</span></div>'
        }
      ]
    }
  ],
  loadRecord: function (record, records, selectedrecords) {
    this.record = record;
    this.records = records;
    this.selectedrecords = selectedrecords;

    console.log('disableNext');
    this.fireEvent('disableNext', true);

    if (typeof this.record.get('__sendmail_info') == 'undefined') {
      me.getComponent('syncform').hide();
      me.getComponent('waitpanel').hide();
      me.getComponent('messagepanel').show();
      return;
    }
    this.fillform(0);
  },
  fillform: async function (index) {

    this.record = this.records[this.record_index = index];
    console.log('Fülle Formular für Datensatz ', index, this.record.getData());
    let res = await fetch('./mail/renderpug', {
      method: 'put',
      body: JSON.stringify(this.record.getData())
    });
    res = await res.json();
    if (res.success) {

      if ((typeof res._hint_message != "undefined") && (res._hint_message !== '')) {
        Ext.toast({
          html: res._hint_message,
          title: 'Hinweis',
          align: 't',
          iconCls: 'fa fa-info-circle'
        });
      }

      if ((typeof res._form_hide_attachments != "undefined") && (res._form_hide_attachments !== '')) {
        this.getComponent('mailform').getComponent('attachments').hide();
      }
      if ((typeof res._form_hide_from != "undefined") && (res._form_hide_from !== '')) {
        this.getComponent('mailform').getComponent('mailfrom').hide();
      }

      console.log('mailform', this.getComponent('mailform').id);

      this.getViewModel().getStore('attachments').setData(res.attachments);


      this.fireEvent('mailformLoaded', res.data);
      this.getComponent('mailform').getForm().setValues(res.data);
      this.getComponent('mailform').enable();
      this.getComponent('waitpanel').hide();

      console.log('enableNext');
      this.fireEvent('enableNext', true);
    } else {
      Ext.toast({
        html: res.msg,
        title: 'Fehler',
        align: 't',
        iconCls: 'fa fa-warning'
      });
    }

  },
  getNextText: function () {
    return 'Alle Senden';
  },
  run: async function () {
    let me = this;
    let o = this.getComponent('mailform').getForm().getValues();
    o.mail_record = this.record.getData();

    me.getComponent('messagepanel').hide();
    me.getComponent('waitpanel').show();
    let res = await fetch('./mail/sendpug', {
      method: 'POST',
      body: JSON.stringify(o)
    });
    res = await res.json();
    if (res.success !== true) {
      Ext.toast({
        html: res.msg,
        title: 'Fehler',
        align: 't',
        iconCls: 'fa fa-warning'
      });
    } else {
      // weiter zum nächsten datensatz
      let index = me.record_index;
      index++;
      if (index < me.records.length) {
        await me.fillform(index);
        await me.run();
        return false; // nicht fertig
      }
    }
    return res;
  }
});
