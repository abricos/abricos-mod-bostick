var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['appModel.js']},
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        L = Y.Lang,
        SYS = Brick.mod.sys;

    NS.Sticker = Y.Base.create('sticker', SYS.AppModel, [], {
        structureName: 'sticker'
    });

    NS.SitckerList = Y.Base.create('stickerList', SYS.AppModelList, [], {
        appItem: NS.Sticker
    });

};