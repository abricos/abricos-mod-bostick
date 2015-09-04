var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['application.js']},
        {name: '{C#MODNAME}', files: ['model.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.roles = new Brick.AppRoles('{C#MODNAME}', {
        isWrite: 30
    });

    SYS.Application.build(COMPONENT, {}, {
        initializer: function(){
            this.initCallbackFire();
        }
    }, [], {
        ATTRS: {
            isLoadAppStructure: {value: true},
            Sticker: {value: NS.Sticker},
            StickerList: {value: NS.StickerList},
        },
        REQS: {
            stickerList: {
                attribute: true,
                type: 'modelList:StickerList'
            },
            stickerSave: {
                args: ['sticker'],
                attribute: false
            },
            stickerRemove: {
                args: ['stickerid'],
                attribute: false
            }
        },
        URLS: {}
    });
};