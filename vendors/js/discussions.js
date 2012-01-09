/*jslint bitwise: true, browser: true, continue: true, unparam: true, rhino: true, sloppy: true, eqeq: true, sub: false, vars: true, white: true, plusplus: true, maxerr: 150, indent: 4 */
/*global laboratree: false, Ext: false */


laboratree.discussions = {};
laboratree.discussions.masks = {};
laboratree.discussions.categories = {};
laboratree.discussions.topics = {};
laboratree.discussions.render = {};
laboratree.discussions.view = {};
laboratree.discussions.editor = {};

/* Plugin Functions */
/* Dashboard Functions */
laboratree.discussions.makePortlet = function(data_url) {
	laboratree.discussions.portlet = new laboratree.discussions.Portlet(data_url);
}

laboratree.discussions.Portlet = function(data_url) {
	Ext.QuickTips.init();

	Ext.state.Manager.setProvider(new Ext.state.CookieProvider({
		expires: new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 7))
	}));

	this.data_url = data_url;

	this.state_id = 'state-' + laboratree.context.table_type + '-dashboard-' + laboratree.context.table_id;

	this.column = 'dashboard-column-right';
	this.position = 1;

	this.store = new Ext.data.GroupingStore({
		autoLoad: true,
		url: data_url,
		baseParams: {
			model: 'discussions'
		},
		reader: new Ext.data.JsonReader({
			root: 'discussions',
			fields: ['id', 'title', 'category', 'parent_id', 'content']
		}),
		groupField: 'category'
	});

	this.portlet = new Ext.grid.GridPanel({
		id: 'portlet-discussions',
		height: 200,
		stripeRows: true,
		loadMask: {msg: 'Loading...'},

		store: this.store,

		autoExpandColumn: 'title',
		cm: new Ext.grid.ColumnModel({
			defaults: {
				sortable: true
			},

			columns: [{
				id: 'title',
				header: 'Topic',
				dataIndex: 'title',
				renderer: this.renderTitle,
			},{
				id: 'category',
				header: 'Category',
				dataIndex: 'category',
				renderer: this.renderCategory,
				hidden: true
			}]
		 }),

		 view: new Ext.grid.GroupingView({
			 forceFit: true,
			 showGroupName: false
		 })
	});

	this.panel = {
		id: 'panel-discussions',
		title: 'Discussions',
		layout: 'fit',

		tools: [{
			id: 'help',
			qtip: 'Help Discussions',
			handler: function(event, toolEl, panel, tc) {
				Ext.Ajax.request({
					url: String.format(laboratree.links.help.site.index, laboratree.context.table_type, 'discussions') + '.json',
					success: function(response, request) {
						var data = Ext.decode(response.responseText);
						if(data.success) {
							laboratree.helpPopup('Discussions Help', data.help.Help.content);
						}
					},
					failure: function() {
					}
				});
			}
		}],

		items: this.portlet,

		listeners: {
			expand: function(p) {
				laboratree.discussions.portlet.toggle(false);
			},
			collapse: function(p) {
				laboratree.discussions.portlet.toggle(true);
			}
		}
	};

	if(laboratree.site.permissions.discussions.view & laboratree.context.permissions.discussion) {
		this.panel.title = '<a href="' + String.format(laboratree.links.discussions[laboratree.context.table_type], laboratree.context.table_id) + '">Discussions</a>';

		this.panel.tools.unshift({
			id: 'restore',
			qtip: 'Discussions Dashboard',
			handler: function() {
				window.location = String.format(laboratree.links.discussions[laboratree.context.table_type], laboratree.context.table_id);
			}
		});
	}

	if(laboratree.site.permissions.discussions.category.add & laboratree.context.permissions.discussion) {
		this.panel.title += '<span class="create-link">';
		this.panel.title += '<a href="' + String.format(laboratree.links.discussions.add, laboratree.context.table_type, laboratree.context.table_id, '') + '">- add category -</a>';
		this.panel.title += '</span>';

		this.panel.tools.unshift({
			id:'plus',
			qtip: 'Add Category',
			handler: function() {
				window.location = String.format(laboratree.links.discussions.add, laboratree.context.table_type, laboratree.context.table_id);
			}
		});
	}

	var states = Ext.state.Manager.get(this.state_id, null);
	if(!states) {
		states = {};
	}

	var state = states.discussions;
	if(!state) {
		state = {
			collapsed: false,
			column: this.column,
			position: this.position
		};
	}

	this.panel.collapsed = state.collapsed;

	var column = Ext.getCmp(state.column);
	if(!column) {
		return false;
	}

	column.insert(state.position, this.panel);
};

laboratree.discussions.Portlet.prototype.renderTitle = function(value, p, record) {
	var permission = laboratree.context.permissions.discussion;
	if(record.data.permission && record.data.permission.discussion) {
		permission = parseInt(record.data.permission.discussion, 10);
	}

	var label = value;
	if(laboratree.site.permissions.discussions.view & permission) {
		label = String.format('<a href="' + laboratree.links.discussions.view + '" title="{2}">{1}</a>', record.data.id, value, record.data.description);
	}

	return label;
};

laboratree.discussions.Portlet.prototype.renderCategory = function(value, p, record) {
	var permission = laboratree.context.permissions.discussion;
	if(record.data.permission && record.data.permission.discussion) {
		permission = parseInt(record.data.permission.discussion, 10);
	}

	var label = value;
	if(laboratree.site.permissions.discussions.view & permission) {
		label = String.format('<a href="' + laboratree.links.discussions.topics + '" title="{2}">{1}</a>', record.data.parent_id, value, record.data.description);
	}

	return label;
};

laboratree.discussions.Portlet.prototype.toggle = function(collapsed) {
	var states = Ext.state.Manager.get(this.state_id, null);
	if(!states) {
		states = {};
	}

	var state = states.discussions;
	if(!state) {
		state = {
			collapsed: false,
			column: this.column,
			position: this.position
		};

		states.discussion = state;
	}

	states.discussion.collapsed = collapsed;

	Ext.state.Manager.set(this.state_id, states);
};

laboratree.discussions.makeCategories = function(title, div, data_url, table_type, table_id) {
	laboratree.discussions.categories = new laboratree.discussions.Categories(title, div, data_url, table_type, table_id);
};

laboratree.discussions.Categories = function(title, div, data_url, table_type, table_id) {
	Ext.QuickTips.init();

	this.div = div;
	this.data_url = data_url;

	this.table_type = table_type;
	this.table_id = table_id;

	this.store = new Ext.data.JsonStore({
		root: 'discussions',
		autoLoad: true,
		url: data_url,

		fields: [
			'id', 'title', 'posts', 'author', 'author_id', 'created', 'lastpost_time', 'lastpost_author', 'lastpost_author_id', 'content'
		],

		listeners: {
			beforeload: function(store, options) {
				laboratree.discussions.masks.discussions = new Ext.LoadMask('discussions', {
					msg: 'Loading...'
				});
				laboratree.discussions.masks.discussions.show();

				return true;
			},
			load: function(store, records, options) {
				laboratree.discussions.masks.discussions.hide();
			}
		}
	});

	this.store.setDefaultSort('created', 'ASC');

	var gridConfig = {
		id: 'discussions',
		title: title,
		renderTo: div,
		width: '100%',
		height: 650,
		stripeRows: true,
		viewConfig: {
			forceFit: true,
			enableRowBody: true,
			getRowClass: function(record, rowIndex, p, store) {
				p.body = record.data.content;
				return 'x-grid3-row-with-body';
			},
			scrollOffset: 1
		},
		store: this.store,

		cm: new Ext.grid.ColumnModel({
			defaults: {
				sortable: true
			},
			columns: [{
				id: 'category',
				header: 'Category',
				dataIndex: 'title',
				width: 365,
				renderer: laboratree.discussions.render.category
			},{
				id: 'topics',
				header: 'Topics',
				dataIndex: 'posts',
				width: 60,
				align: 'center'
			},{
				id: 'lastpost',
				header: 'Last Post',
				dataIndex: 'lastpost_time',
				width: 310,
				renderer: laboratree.discussions.render.lastpost
			},{
				id: 'actions',
				header: 'Actions',
				dataIndex: 'id',
				width: 100,
				align: 'center',
				renderer: laboratree.discussions.render.actions.categories
			}]
		}),

		tools: [{
			id: 'refresh',
			qtip: 'Refresh Discussion Categories',
			handler: function(event, toolEl, panel, tc) {
				laboratree.discussions.categories.store.reload();
			}
		}],

		bbar: {
			xtype: 'paging',
			pageSize: 30,
			store: this.store,
			displayInfo: true,
			displayMsg: 'Displaying discussion category {0} - {1} of {2}',
			emptyMsg: 'No discussion categories to display'
		}
	};

	if(laboratree.site.permissions.discussions.category.add & laboratree.context.permissions.discussion) {
		gridConfig.tools.unshift({
			id:'plus',
			qtip: 'Add Discussion Category',
			handler: function() {
				window.location = String.format(laboratree.links.discussions.add, table_type, table_id, '');
			}
		});
	}

	this.grid = new Ext.grid.GridPanel(gridConfig);
};

laboratree.discussions.render.category = function(value, p, record) {
	return String.format('<a href="' + laboratree.links.discussions.topics + '" title="{1}">{1}</a>', record.id, value);
};

laboratree.discussions.render.lastpost = function(value, p, record) {
	return String.format('<a href="' + laboratree.links.users.profile + '" title="{1}">{1}</a> {2}', record.data.lastpost_author_id, record.data.lastpost_author, value);
};

laboratree.discussions.render.actions = {};

laboratree.discussions.render.actions.categories = function(value, p, record) {
	var actions = '';

	if(laboratree.site.permissions.discussions.category.edit & laboratree.context.permissions.discussion) {
		actions += '<a href="' + laboratree.links.discussions.edit + '" title="Edit Category">Edit</a>';
	}

	if(laboratree.site.permissions.discussions.category['delete'] & laboratree.context.permissions.discussion) {
		if(actions !== '') {
			actions += ' | ';
		}

		actions += '<a href="#" onclick="laboratree.discussions.categories.remove({0}); return false;" title="Delete Category">Delete</a>';
	}

	return String.format(actions, value);
};

laboratree.discussions.render.actions.topics = function(value, p, record) {
	var actions = '';

	if(laboratree.site.permissions.discussions.topic.edit & laboratree.context.permissions.discussion) {
		actions += '<a href="' + laboratree.links.discussions.edit + '" title="Edit Topic">Edit</a>';
	}

	if(laboratree.site.permissions.discussions.topic['delete'] & laboratree.context.permissions.discussion) {
		if(actions !== '') {
			actions += ' | ';
		}

		actions += '<a href="#" onclick="laboratree.discussions.topics.remove({0}); return false;" title="Delete Topic">Delete</a>';
	}

	return String.format(actions, value);
};

laboratree.discussions.Categories.prototype.remove = function(article_id) {
	if(window.confirm('Are you sure?')) {
		Ext.Ajax.request({
			url: String.format(laboratree.links.discussions['delete'], article_id) + '.json',
			success: function(response, request) {
				var data = Ext.decode(response.responseText);
				if(!data) {
					request.failure(response, request);
					return false;
				}

				if(!data.success) {
					request.failure(response, request);
					return false;
				}

				var record = laboratree.discussions.categories.store.getById(article_id);
				if(record) {
					laboratree.discussions.categories.store.remove(record);
				}
			},
			failure: function(response, request) {
			},
			scope: this
		});
	}
};

laboratree.discussions.makeTopics = function(title, div, data_url, table_type, table_id, category_id) {
	laboratree.discussions.topics = new laboratree.discussions.Topics(title, div, data_url, table_type, table_id, category_id);
};

laboratree.discussions.Topics = function(title, div, data_url, table_type, table_id, category_id) {
	Ext.QuickTips.init();

	this.div = div;
	this.data_url = data_url;

	this.table_type = table_type;
	this.table_id = table_id;
	this.category_id = category_id;

	this.store = new Ext.data.JsonStore({
		root: 'discussions',
		autoLoad: true,
		url: data_url,
		fields: [
			'id', 'title', 'posts', 'author', 'author_id', 'lastpost_time', 'lastpost_author', 'lastpost_author_id', 'content'
		],

		listeners: {
			beforeload: function(store, options) {
				laboratree.discussions.masks.discussions = new Ext.LoadMask('discussions', {
					msg: 'Loading...'
				});
				laboratree.discussions.masks.discussions.show();

				return true;
			},
			load: function(store, records, options) {
				laboratree.discussions.masks.discussions.hide();
			}
		}
	});

	this.store.setDefaultSort('lastpost_time', 'ASC');

	var gridConfig = {
		id: 'discussions',
		title: title,
		renderTo: div,
		width: '100%',
		height: 600,
		stripeRows: true,

		viewConfig: {
			forceFit: true,
			enableRowBody: true,
			getRowClass: function(record, rowIndex, p, store) {
				p.body = record.data.content;
				return 'x-grid3-row-with-body';
			},
			scrollOffset: 1
		},

		store: this.store,

		cm: new Ext.grid.ColumnModel({
			defaults: {
				sortable: true
			},
			columns: [{
				id: 'topic',
				header: 'Topic',
				dataIndex: 'title',
				width: 365,
				renderer: laboratree.discussions.render.topics
			},{
				id: 'posts',
				header: 'Posts',
				dataIndex: 'posts',
				width: 60
			},{
				id: 'lastpost',
				header: 'Last Post',
				dataIndex: 'lastpost_time',
				width: 310,
				renderer: laboratree.discussions.render.lastpost
			},{
				id: 'actions',
				header: 'Actions',
				dataIndex: 'id',
				width: 100,
				align: 'center',
				renderer: laboratree.discussions.render.actions.topics
			}]
		}),

		tools: [{
			id: 'refresh',
			qtip: 'Refresh Discussion Topics',
			handler: function(e, toolEl, panel, tc) {
				laboratree.discussions.topics.store.reload();
			}
		}],

		bbar: new Ext.PagingToolbar({
			pageSize: 30,
			store: this.store,

			displayInfo: true,
			displayMsg: 'Displaying discussion topic {0} - {1} of {2}',
			emptyMsg: 'No discussion topics to display'
		})
	};

	if(laboratree.site.permissions.discussions.topic.add & laboratree.context.permissions.discussion) {
		gridConfig.tools.unshift({
			id: 'plus',
			qtip: 'Add Discussion Topic to Category',
			handler: function(e, toolEl, panel, tc) {
				window.location = String.format(laboratree.links.discussions.add, table_type, table_id, category_id);
			}
		});
	}

	this.grid = new Ext.grid.GridPanel(gridConfig);
};

laboratree.discussions.Topics.prototype.remove = function(article_id) {
	if(window.confirm('Are you sure?')) {
		Ext.Ajax.request({
			url: String.format(laboratree.links.discussions['delete'], article_id) + '.json',
			success: function(response, request) {
				var data = Ext.decode(response.responseText);
				if(!data) {
					request.failure(response, request);
					return false;
				}

				if(!data.success) {
					request.failure(response, request);
					return false;
				}

				var record = laboratree.discussions.topics.store.getById(article_id);
				if(record) {
					laboratree.discussions.topics.store.remove(record);
				}
			},
			failure: function(response, request) {
			},
			scope: this
		});
	}
};

laboratree.discussions.render.topics = function(value, p, record) {
	return String.format('<a href="' + laboratree.links.discussions.view + '" title="{1}">{1}</a>', record.id, value);
};

laboratree.discussions.render.lastpost = function(value, p, record) {
	return String.format('<a href="' + laboratree.links.users.profile + '" title="{1}">{1}</a> {2}', record.data.author_id, record.data.author, value);
};

laboratree.discussions.makeView = function(div, title, discussion_id, data_url) {
	laboratree.discussions.view = new laboratree.discussions.View(div, title, discussion_id, data_url);
};

laboratree.discussions.View = function(div, title, discussion_id, data_url) {
	Ext.QuickTips.init();

	this.div = div;
	this.title = title;
	this.discussion_id = discussion_id;
	this.data_url = data_url;

	var formConfig = {
		xtype: 'form',
		id: 'discussion-reply',
		title: 'Reply:',

		frame: true,
		anchor: '100%',
		height: 250,

		items: [{
			id: 'discussion-textarea',
			xtype: 'htmleditor',
			enableColors: true,
			enableAlignments: true,
			fieldLabel: 'Post',
			hideLabel: true,
			anchor: '100% 100%',
			name: 'data[Discussion][content]'
		}],

		buttons: []
	};

	if(laboratree.site.permissions.discussions.post.add & laboratree.context.permissions.discussion) {
		formConfig.buttons.unshift({
			text: 'Add Post',
			handler: function () {
				if(laboratree.discussions.view.form.getForm().isValid()) {
					laboratree.discussions.view.form.getForm().submit({
						url: String.format(laboratree.links.discussions.view, laboratree.discussions.view.discussion_id) + '.json',
						waitMsg: 'Adding Discussion Post...',
						success: function(form, action) {
							laboratree.discussions.view.addPost(action.result.discussion, laboratree.discussions.view.panel);
							var textarea = form.items.items[0];
							if(textarea) {
								textarea.setValue('');
								textarea.focus();
							}
						},
						failure: function(form, action) {
							switch(action.failureType) {
								case Ext.form.Action.CLIENT_INVALID:
									break;
								case Ext.form.Action.CONNECT_FAILURE:
									break;
								case Ext.form.Action.SERVER_INVALID:
									break;
							}
						}
					});
				}
			}

		});
	}

	this.form = new Ext.form.FormPanel(formConfig);

	this.panel = new Ext.Panel({
		id: 'discussion-panel',
		renderTo: this.div,
		border: false,

		width: '100%',
		autoHeight: true,

		items: this.form
	});

	Ext.Ajax.request({
		url: data_url,
		success: function(response, request) {
			var data = Ext.decode(response.responseText);
			if(!data) {
				request.failure(response, request);
				return false;
			}

			if(data.length < 1) {
				request.failure(response, request);
			}

			var discussion = data[0];

			this.addPost(discussion, this.panel, title);

			if(discussion.children) {
				var children = discussion.children;
				Ext.each(children, function(item, index, allItems) {
					this.addPost(item, this.panel, 'Re: ' + title);
				}, this);
			}
			this.panel.doLayout();
		},
		failure: function(response, request) {

		},
		scope: this
	});
};

laboratree.discussions.View.prototype.addPost = function(discussion, container, title) {
	var position = container.items.items.length - 1;

	var picture = '/img/users/default_small.png';
	if(discussion.Author.picture) {
		picture = '/img/users/' + discussion.Author.picture + '_thumb.png';
	}

	var posted = new Date();
	posted = Date.parseDate(discussion.Discussion.created, 'Y-m-d H:i:s');

	var post = {
		id: 'discussion-' + discussion.Discussion.id,
		title: posted.format('m/d/Y h:ia') + ' - ' + title,
		height: 200,

		frame: true,

		layout: 'hbox',
		layoutConfig: {
			pack: 'start',
			align: 'stretch'
		},

		items: [{
			layout: 'vbox',
			flex: 1,
			width: 150,

			layoutConfig: {
				pack: 'start',
				align: 'middle'
			},
			items:[{
				height: 135,
				html: '<img src="' + picture + '" style="height: 125px; width: 125px; border: 2px solid #000000; margin-left: 6px;" alt="' + discussion.Author.name + '" />'
			},{
				bodyStyle: 'padding: 2px 0 0; width: 125px; text-align: center; font-weight: bold; margin-left: 6px;',
				html: discussion.Author.name
			}]
		},{
			flex: 1,

			items: [{
				flex: 1,
				bodyStyle: 'padding: 2px; background-color: #fff;',
				html: discussion.Discussion.content,
				frame: true,
				height: 130
			}]
		}]
	};

	var buttons = [];
	if(laboratree.site.permissions.discussions.post.edit & laboratree.context.permissions.discussion) {
		buttons.push({
			xtype: 'button',
			text: 'Edit',
			discussion: discussion,
			handler: function(btn, e) {
				window.location = String.format(laboratree.links.discussions.edit, btn.discussion.Discussion.id);
			}
		});
	}

	if(laboratree.site.permissions.discussions.post['delete'] & laboratree.context.permissions.discussion) {
		if(buttons.length > 0) {
			buttons.push({
				xtype: 'tbseparator'
			});
		}

		buttons.push({
			xtype: 'button',
			text: 'Delete',
			discussion: discussion,
			handler: function(btn, e) {
				laboratree.discussions.view.deletePost(btn.discussion.Discussion.id, btn.discussion.Discussion.parent_id, btn.discussion.Discussion.discussion_type);
			}
		});
	}

	if(buttons.length > 0) {
		post.items[1].tbar = {
			buttonAlign: 'right',
			items: buttons
		};
	}

	container.insert(position, post);
	container.doLayout();
};

laboratree.discussions.View.prototype.deletePost = function(discussion_id, parent_id, discussion_type) {
	if(window.confirm('Are you sure?')) {
		Ext.Ajax.request({
			url: String.format(laboratree.links.discussions['delete'], discussion_id) + '.json',
			success: function(response, request) {
				var data = Ext.decode(response.responseText);
				if(data.error) {
					request.failure();
					return;
				}

				if(discussion_type == 'topic')
				{
					window.location = String.format(laboratree.links.discussions.topics, parent_id);
					return;
				}

				var panel = Ext.getCmp('discussion-panel');
				if(panel) {
					panel.remove('discussion-' + discussion_id);
					panel.doLayout();
				}
			},
			failure: function(response, request) {

			}
		}, this);
	}
};

/* Permissions for makePost are checked in the controller */
laboratree.discussions.makePost = function(div, table_type, table_id, data_url,type) {
	laboratree.discussions.post = new laboratree.discussions.Post(div, table_type, table_id, data_url, type);
};

laboratree.discussions.Post = function(div, table_type, table_id, data_url, type) {
	Ext.QuickTips.init();

	this.div = div;
	this.table_type = table_type;
	this.table_id = table_id;
	this.data_url = data_url;
	this.type = type;

	this.stores = {};

	this.form = new Ext.FormPanel({
		labelAlign: 'top',
		autoHeight: true,
		title: 'Add Discussion ' + this.type,
		renderTo: this.div,
		buttonAlign: 'center',
		frame: true,
		fileUpload: true,
		standardSubmit: true,
		forceLayout: true,
		layout: 'form',

		defaults: {
			forceLayout: true
		},

		items: [{
			id: 'DiscussionTitle',
			xtype: 'textfield',
			fieldLabel: 'Title',
			name: 'data[Discussion][title]',
			allowBlank: false,
			maxLength: 255,
			anchor: '100%',
			vtype: 'discussionTitle'
		},{
			id: 'DiscussionDescription',
			xtype: 'textarea',
			fieldLabel: 'Content',
			name: 'data[Discussion][content]',
			height: 100,
			anchor: '100%',
			vtype: 'discussionContent'
		}],
		buttons: [{
			text: 'Add Discussion ' + this.type,
			handler: function () {
				if(laboratree.discussions.post.form.getForm().isValid()) {
					laboratree.discussions.post.form.getForm().submit({
						url: laboratree.discussions.post.data_url
					});
				}
			}
		}]
	});
};

/* Permissions for makeEditor is checked in the controller */
laboratree.discussions.makeEditor = function(container_div, discussion_id, data_url, typeName) {
	Ext.onReady(function(){
		laboratree.discussions.editor = new laboratree.discussions.Editor(container_div, discussion_id, data_url, typeName);
		Ext.Ajax.request({
			url: data_url,
			success: function(response, request) {
				var data = Ext.decode(response.responseText);

				if(!data) {
					request.failure(response, request);
					return;
				}

				if(data.error) {
					request.failure(response, request);
					return;
				}

				var title = Ext.getCmp('DiscussionTitle');
				if(title) {
					title.setValue(data.title);
				}

				var content = Ext.getCmp('DiscussionContent');
				if(content) {
					content.setValue(data.content);
				}
			},
			failure: function(response, request) {

			}
		});
	});
};

laboratree.discussions.Editor = function(container_div, discussion_id, data_url, typeName) {
	Ext.QuickTips.init();

	this.form = new Ext.form.FormPanel({
		title: 'Edit Discussion' + typeName,
		renderTo: container_div,
		width: '100%',
		autoHeight: true,
		standardSubmit: true,
		frame: true,

		buttonAlign: 'center',
		defaultType: 'textfield',

		defaults: {
			anchor: '100%'
		},

		items: [{
			id: 'DiscussionTitle',
			fieldLabel: 'Title',
			name: 'data[Discussion][title]',
			vtype: 'discussionTitle'
		},{
			id: 'DiscussionContent',
			fieldLabel: 'Content',
			xtype: 'textarea',
			name: 'data[Discussion][content]',
			vtype: 'discussionContent'
		}],

		buttons: [{
			text: 'Update',
			handler: function() {
				if(laboratree.discussions.editor.form.getForm().isValid()) {
					laboratree.discussions.editor.form.getForm().submit({
						url: String.format(laboratree.links.discussions.edit, discussion_id) + '.json'
					});
				}
			}
		}]
	});
};
