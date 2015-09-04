var Component = new Brick.Component();
Component.requires = {
    yui: ['dd-drag'],
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){
    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    var BBOX = 'boundingBox';

    NS.convertViewToInput = function(str){
        var ret = str.replace(/&lt;/gi, '<')
            .replace(/&gt;/gi, '>')
            .replace(/\n/gi, '')
            .replace(/\r/gi, '')
            .replace(/<br \/>/gi, '\n')
            .replace(/<br\/>/gi, '\n')
            .replace(/<br>/gi, '\n');
        return ret;
    };

    NS.convertInputToView = function(str){
        return str.replace(/</gi, '&lt;')
            .replace(/>/gi, '&gt;')
            .replace(/\n/gi, '<br />');
    }

    NS.StickerWidget = Y.Base.create('stickerWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance){
            var bBox = this.get(BBOX);
            bBox.addClass('aw-bostick-w');
            var dd = new Y.DD.Drag({node: bBox});

            dd.on('start', this.moveTop, this);
            dd.on('end', this.save, this);
            bBox.on('dblclick', this.showEditor, this);

            var stickerid = this.get('stickerid'),
                tp = this.template;

            if (stickerid === 0){
                var ord = NS.Board.instance.list.length;
                this.set('order', ord + 1);
                this.save();
            } else {
                var sticker = appInstance.get('stickerList').getById(stickerid),
                    text = sticker.get('body');

                this.set('region', sticker.get('region'));
                tp.setHTML('view', text);
                tp.setValue('input', NS.convertViewToInput(text));
            }
        },
        onClick: function(){
            this.moveTop();
        },
        moveTop: function(){
            NS.Board.instance.stickerMoveTop(this.get('stickerid'));
        },
        getInputNode: function(){
            return this.template.one('input');
        },
        showEditor: function(){
            if (this._isShowEditor){
                return;
            }
            this._isShowEditor = true;
            var tp = this.template;
            tp.toggleView(true, 'input', 'view,bremove');
            tp.one('input').focus();
        },
        closeEditor: function(){
            if (!this._isShowEditor){
                return;
            }
            this._isShowEditor = false;
            var tp = this.template;
            tp.toggleView(false, 'input', 'view,bremove');

            var text = tp.getValue('input');

            tp.setHTML('view', NS.convertInputToView(text));
            this.save();
        },
        remove: function(){
            NS.Board.instance.stickerRemove(this.get('stickerid'));
        },
        save: function(){
            if (this.get('waiting')){
                return;
            }
            var text = this.template.getValue('input'),
                d = {
                    id: this.get('stickerid'),
                    region: this.get('region'),
                    body: NS.convertInputToView(text),
                    ord: this.get('order')
                };

            this.set('waiting', true);
            this.get('appInstance').stickerSave(d, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.set('stickerid', result.stickerSave.stickerid);
                }
            }, this);
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget'},
            stickerList: {
                readOnly: true,
                getter: function(){
                    var app = this.get('appInstance');
                    if (!app){
                        return;
                    }
                    return app.get('stickerList');
                }
            },
            stickerid: {
                validator: Y.Lang.isNumber,
                value: 0
            },
            region: {
                validator: Y.Lang.isString,
                setter: function(val){
                    var bbox = this.get(BBOX);
                    if (!bbox){
                        return;
                    }
                    var a = val.split(',');
                    bbox.setXY([a[0], a[1]]);
                },
                getter: function(){
                    var w = this.get(BBOX).get('region'),
                        a = [w.left, w.top, w.width, w.height];
                    return a.join(',');
                }
            },
            order: {
                setter: function(zIndex){
                    this.get(BBOX).setStyle('zIndex', zIndex | 0);
                },
                getter: function(){
                    return this.get(BBOX).getStyle('zIndex') | 0;
                }
            }
        },
        CLICKS: {
            'remove': 'remove'
        }
    });

    var Board = function(options, callback, context){
        NS.Board.instance = this;

        NS.initApp({
            initCallback: function(err, appInstance){
                appInstance.stickerList(function(err1, result){
                    NS.Board.instance._init(options, appInstance, result.stickerList);
                    callback.call(context || null);
                });
            }
        });
    };
    Board.instance = null;
    Board.prototype = {
        _init: function(options, appInstance, stickerList){
            options = options || {};
            this.appInstance = appInstance;
            this.stickerList = stickerList;
            this.list = [];

            var nodeBody = Y.Node.one(document);
            nodeBody.on('click', this._closeEditors, this);
        },
        _createDiv: function(){
            return Y.Node.one(document.body).appendChild('<div></div>');
        },
        _showSticker: function(stickerid){
            this.list[this.list.length] = new NS.StickerWidget({
                srcNode: this._createDiv(),
                stickerid: stickerid | 0
            });
        },
        createSticker: function(){
            // var nsOpt = this.newStickOptions;
            this._showSticker();
        },
        showStickers: function(){
            this.stickerList.each(function(sticker){
                this._showSticker(sticker.get('id'));
            }, this);
        },
        each: function(fn, context){
            var list = this.list;
            for (var i = 0; i < list.length; i++){
                fn.call(context || this, list[i].get('stickerid'), list[i], i);
            }
        },
        getStickerWidget: function(stickerid){
            stickerid = stickerid | 0;
            var list = this.list;
            for (var i = 0; i < list.length; i++){
                if (list[i].get('stickerid') === stickerid){
                    return list[i];
                }
            }
            return null;
        },
        stickerRemove: function(stickerid){
            stickerid = stickerid | 0;
            var widget = this.getStickerWidget(stickerid);
            if (!widget){
                return;
            }
            widget.destroy();

            var nList = [], list = this.list;
            for (var i = 0; i < list.length; i++){
                if (list[i].get('stickerid') !== stickerid){
                    nList[nList.length] = list[i];
                }
            }
            this.list = nList;

            var appInstance = this.appInstance,
                stickerList = appInstance.get('stickerList');

            appInstance.stickerRemove(stickerid, function(err, result){
                if (!err){
                    stickerList.removeById(stickerid);
                }
            }, this);
        },
        _closeEditors: function(e){
            for (var i = 0, list = this.list; i < list.length; i++){
                if (e.target.get('id') !== list[i].getInputNode().get('id')){
                    list[i].closeEditor();
                }
            }
        },
        stickerMoveTop: function(stickerid){
            var widget = this.getStickerWidget(stickerid);

            if (!widget){
                return;
            }

            var list = this.list;
            if (list.length > 0 && list[list.length - 1].get('stickerid') === stickerid){
                return;
            }

            var nlist = [], ords = {},
                index = 1;

            this.each(function(id, item){
                if (stickerid === id){
                    return;
                }
                nlist[nlist.length] = item;
                item.set('order', index);
                ords[id] = index;
                index++;
            }, this);

            nlist[nlist.length] = widget;
            this.list = nlist;

            widget.set('order', index);
            ords[stickerid] = index;

            this.appInstance.stickersOrderSave(ords);
        }
    };
    NS.Board = Board

    NS.createSticker = function(){
        if (NS.Board.instance){
            NS.Board.instance.createSticker();
        } else {
            new NS.Board({}, function(){
                NS.Board.instance.createSticker();
            });
        }
    }

    NS.initializeBoard = function(options, callback, context){
        options = options || {};
        if (options.workspaceWidget){
            options.target = options.workspaceWidget.get('boundingBox');
        }
        if (NS.Board.instance){
            callback.call(context || null);
        } else {
            new NS.Board(options, function(){
                NS.Board.instance.showStickers();
                callback.call(context || null);
            });
        }
    };

};