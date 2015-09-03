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

    NS.StickerWidget = Y.Base.create('stickerWidget', SYS.AppWidget, [], {
        initializer: function(){
            var bBox = this.get('boundingBox');
            bBox.addClass('aw-bostick-w');
            new Y.DD.Drag({
                node: bBox
            });
        },
        onInitAppWidget: function(err, appInstance){

        },
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget'}
        }
    });

    var Board = function(options, callback, context){
        NS.Board.instance = this;

        NS.initApp({
            initCallback: function(err, appInstance){
                appInstance.stickerList(function(err1, result){
                    NS.Board.instance._init(options, result.stickerList);
                    callback.call(context || null);
                });
            }
        });
    };
    Board.instance = null;
    Board.prototype = {
        _init: function(options, stickerList){
            options = options || {};
            this.stickerList = stickerList;

            var nsOpt = this.newStickOptions = {
                x: 300,
                y: 300
            };
            var target = options.target;

            if (target){
                var xy = options.target.getXY(),
                    w = Math.max(options.target.get('offsetWidth'), 500);
                nsOpt.x = w / 2 + xy[0] + 200;
            }
        },
        createSticker: function(){
            var div = Y.Node.one(document.body).appendChild('<div></div>'),
                nsOpt = this.newStickOptions;

            new NS.StickerWidget({
                srcNode: div
            });
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
            new NS.Board(options, callback, context);
        }
    };

};