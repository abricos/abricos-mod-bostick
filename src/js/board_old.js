var Component = new Brick.Component();
Component.requires = {};
Component.entryPoint = function(NS){

    var StickWidget = function(data){

        data = L.merge({
            'id': 0,
            'rg': '0,0,318,168',
            'bd': ''
        }, data || {});
        this.init(data);
    };
    StickWidget.prototype = {
        init: function(data){
            this._isEditMode = false;

            buildTemplate(this, 'widget');
            var div = document.createElement('div');
            div.innerHTML = this._TM.replace('widget');
            var el = div.childNodes[0];
            document.body.appendChild(el);

            var __self = this;
            E.on(el, 'click', function(e){
                var el = E.getTarget(e);
                if (__self.onClick(el)){
                    E.preventDefault(e);
                }
            });

            E.on(el, 'dblclick', function(e){
                var el = E.getTarget(e);
                if (__self.onDblClick(el)){
                    E.preventDefault(e);
                }
            });
            this.element = el;

            if (data['id'] == 0){
                var rg = Dom.getClientRegion();
                var x = rg.left + rg.width / 2 - 218 / 2,
                    y = rg.top + rg.height / 2 - 138 / 2;

                data['rg'] = x + ',' + y + ',218,168';
            }
            this.updateData(data);

            Dom.setStyle(el, 'display', '');
        },
        updateData: function(d){
            this.id = d['id'];
            this.data = d;
            var rg = d['rg'].split(',');
            this.setPosition(rg[0], rg[1]);
            this.setValue(d['bd']);
        },
        onClick: function(el){
            var tp = this._TId['widget'];
            switch (el.id) {
                case tp['bremove']:
                    this.showRemoveDialog();
                    return true;
            }
            return false;
        },
        onDblClick: function(el){
            var tp = this._TId['widget'];
            switch (el.id) {
                case tp['id']:
                case tp['bd']:
                case tp['view']:
                    this.setEditMode();
                    return true;
            }

            return false;
        },
        setEditMode: function(){ // установить режим редактирования
            if (this._isEditMode){
                return;
            }
            this._isEditMode = true;

            var TM = this._TM,
                elText = TM.getEl('widget.view'),
                elInput = TM.getEl('widget.input');

            Dom.replaceClass(TM.getEl('widget.bd'), 'mv', 'me');

            var str = elText.innerHTML;
            this._oldText = str;
            str = str.replace(/&lt;/gi, '<').replace(/&gt;/gi, '>');
            str = str.replace(/<br \/>/gi, '\n');
            str = str.replace(/<br\/>/gi, '\n');
            str = str.replace(/<br>/gi, '\n');

            elInput.value = str;

            Dom.setStyle(elText, 'display', 'none');
            Dom.setStyle(elInput, 'display', '');

            try {
                elInput.focus();
            }
            catch (e) {
            }

            E.addListener(elInput, "blur", this.setViewMode, this, true);
        },
        setViewMode: function(){
            if (!this._isEditMode){
                return;
            }
            this._isEditMode = false;

            var TM = this._TM,
                elText = TM.getEl('widget.view'),
                elInput = TM.getEl('widget.input');

            Dom.replaceClass(TM.getEl('widget.bd'), 'me', 'mv');

            var str = elInput.value;
            str = str.replace(/</gi, '&lt;').replace(/>/gi, '&gt;');
            str = str.replace(/\n/gi, '<br />');

            elText.innerHTML = str;

            if (this._oldText != str){
                this.save();
                // this.fireChangedEvent('changefeature', this);
            }

            Dom.setStyle(elText, 'display', '');
            Dom.setStyle(elInput, 'display', 'none');

            E.removeListener(elInput, "blur", this.setViewMode);
        },
        isEditMode: function(){
            return this._isEditMode;
        },
        checkInElement: function(el){
            var tp = this._TId['widget'];
            switch (el.id) {
                case tp['id']:
                case tp['bd']:
                case tp['view']:
                    return true;
            }

            return false;
        },
        getValue: function(){
            return this._TM.getEl('widget.view').innerHTML;
        },
        setValue: function(value){
            this._TM.getEl('widget.view').innerHTML = value;
        },
        getPosition: function(){
            var rg = Dom.getRegion(this.element);
            return [rg.left, rg.top];
        },
        setPosition: function(x, y){
            var el = this.element;
            Dom.setStyle(el, 'left', x + 'px');
            Dom.setStyle(el, 'top', y + 'px');
        },
        getSize: function(){
            var rg = Dom.getRegion(this.element);
            return [rg.width, rg.height];
        },
        setSize: function(w, y){
            var el = this.element;
            Dom.setStyle(el, 'width', w + 'px');
            Dom.setStyle(el, 'height', h + 'px');
        },
        move: function(dx, dy){
            var el = this.element;
            var rg = Dom.getRegion(el);
            Dom.setStyle(el, 'left', (rg.left + dx) + 'px');
            Dom.setStyle(el, 'top', (rg.top + dy) + 'px');
        },
        top: function(){
            var pnode = this.element.parentNode;
            pnode.removeChild(this.element);
            pnode.appendChild(this.element);
        },
        showRemoveDialog: function(){
            this.remove();
        },
        remove: function(){
            var pnode = this.element.parentNode;
            pnode.removeChild(this.element);
            NS.stickManager.removeStickMethod(this);
        },
        save: function(callback){
            callback = L.isFunction(callback) ? callback : function(){
            };
            var pos = this.getPosition(),
                size = this.getSize();

            var d = this.data;

            var sd = {
                'id': d['id'],
                'rg': pos[0] + ',' + pos[1] + ',' + size[0] + ',' + size[1],
                'bd': this._TM.getEl('widget.view').innerHTML
            };
            var change = false;
            for (var n in sd){
                if (sd[n] != d[n]){
                    change = true;
                    break;
                }
            }

            if (!change){ // нечего сохранять
                callback(false);
                return;
            }

            var __self = this;
            Brick.ajax('bostick', {
                'data': {
                    'do': 'sticksave',
                    'stick': sd
                },
                'event': function(request){
                    if (L.isObject(request.data) && request.data['id'] * 1 > 0){
                        __self.updateData(request.data);
                    }
                    callback(true);
                }
            });
        }
    };
    NS.StickWidget = StickWidget;

    var IconWidget = function(container){
        this.init(container);
    };
    IconWidget.prototype = {
        init: function(container){

            buildTemplate(this, 'icon');
            var div = document.createElement('div');
            div.innerHTML = this._TM.replace('icon');
            var el = div.childNodes[0];
            container.appendChild(el);

            var __self = this;
            E.on(el, 'click', function(e){
                __self.onClick(E.getTarget(e));
                E.preventDefault(e);
            });
        },
        onClick: function(el){
            API.addStick();
        }
    };
    NS.IconWidget = IconWidget;

    var StickManager = function(callback){
        this.init(callback);
    };
    StickManager.prototype = {
        init: function(callback){

            this.list = [];
            this._dragStick = null;
            this._lastXY = null;

            var __self = this;
            E.on(document.body, 'mouseup', function(e){
                var el = E.getTarget(e);
                if (__self.onMouseUp(el, e)){
                    E.preventDefault(e);
                }
            });
            E.on(document.body, 'mousedown', function(e){
                var el = E.getTarget(e);
                if (__self.onMouseDown(el, e)){
                    E.preventDefault(e);
                }
            });
            E.on(document.body, 'mousemove', function(e){
                var el = E.getTarget(e);
                if (__self.onMouseMove(el, e)){
                    E.preventDefault(e);
                }
            });


            var __self = this;

            Brick.ajax('bostick', {
                'data': {
                    'do': 'init'
                },
                'event': function(request){
                    __self._initData(request.data);
                    if (L.isFunction(callback)){
                        callback(__self);
                    }
                }
            });
        },
        _initData: function(data){
            if (L.isNull(data)){
                return;
            }
            var sticks = data['sticks'] || [],
                orders = data['orders'] || '';

            var sob = {};
            for (var i = 0; i < sticks.length; i++){
                var di = sticks[i];
                sob[di['id']] = di;
            }
            var ids = orders.split(',');
            for (var i in ids){
                var di = sob[ids[i]];

                if (di){
                    var stick = new StickWidget(di);
                    this.addStick(stick);
                    di['_init'] = true;
                }
            }
            for (var i = 0; i < sticks.length; i++){
                var di = sticks[i];
                if (!di['_init']){
                    var stick = new StickWidget(di);
                    this.addStick(stick);
                }
            }

        },
        stickByElement: function(el){
            var rst = null;
            this.foreach(function(stick){
                if (stick.checkInElement(el)){
                    rst = stick;
                    return true;
                }
            });
            return rst;
        },
        _getXY: function(evt){
            var xy = YAHOO.util.Event.getXY(evt);
            var xy1 = Dom.getXY(document.body);

            return [Math.max(xy[0] - xy1[0], 0), Math.max(xy[1] - xy1[1], 0)];
        },
        onMouseDown: function(el, e){
            this._dragStick = this.stickByElement(el);
            if (!L.isNull(this._dragStick)){
                this._lastXY = this._getXY(e);

                this.moveTop(this._dragStick);

            }
            return false;
        },
        onMouseMove: function(el, e){
            var stick = this._dragStick,
                xy = this._getXY(e),
                lxy = this._lastXY;

            if (L.isNull(stick)){
                return false;
            }
            this._lastXY = xy;

            stick.move(xy[0] - lxy[0], xy[1] - lxy[1]);

            return false;
        },
        onMouseUp: function(el, e){
            var stick = this._dragStick;
            if (!L.isNull(stick)){
                stick.save();
            }
            this._dragStick = null;
            return false;
        },
        moveTop: function(stick){
            if (!stick || L.isNull(stick)){
                return;
            }

            var lst = this.list;
            if (lst.length < 2 || lst[lst.length - 1] == stick){ // он уже поверх всех
                return;
            }
            stick.top();

            var nlst = [], orders = [];
            for (var i = 0; i < lst.length; i++){
                if (lst[i] != stick){
                    nlst[nlst.length] = lst[i];
                    orders[orders.length] = lst[i].id;
                }
            }
            nlst[nlst.length] = stick;
            orders[orders.length] = stick.id;

            this.list = nlst;

            Brick.ajax('bostick', {
                'data': {
                    'do': 'stickordupd',
                    'order': orders.join(',')
                },
                'event': function(request){
                }
            });
        },
        get: function(index){
            index = index * 1;
            if (index < 0 || index >= this.list.length){
                return null;
            }
            return this.list[index];
        },
        count: function(){
            return this.list.length;
        },
        foreach: function(f){
            if (!L.isFunction(f)){
                return;
            }
            var lst = this.list;
            for (var i = 0; i < lst.length; i++){
                if (f(lst[i])){
                    return;
                }
            }
        },
        addStick: function(stick){
            this.list[this.list.length] = stick;
        },
        createStick: function(){
            var stick = new StickWidget();
            this.addStick(stick);
            stick.setEditMode();
            return stick;
        },
        removeStick: function(stick){
            stick.remove();
        },
        removeStickMethod: function(stick){
            var lst = this.list;
            var nlst = [];
            for (var i = 0; i < lst.length; i++){
                if (lst[i] != stick){
                    nlst[nlst.length] = lst[i];
                }
            }
            this.list = nlst;

            Brick.ajax('bostick', {
                'data': {
                    'do': 'stickremove',
                    'stickid': stick.id
                }
            });
        }
    };
    NS.StickManager = StickManager;
    NS.stickManager = null;



};