/*
@version $Id$
@copyright Copyright (C) 2011 Brickos Ltd. All rights reserved.
*/

var Component = new Brick.Component();
Component.entryPoint = function(){
	
	var os = Brick.mod.bos;
	os.ApplicationManager.startupAfterRegister(function(){
		var wks = Brick.mod.bos.LabelListWidget.instance;
		Brick.f('bostick', 'board', 'showIconWidget', wks.element);
	});
	
};
