/*
@version $Id$
@copyright Copyright (C) 2011 Brickos Ltd. All rights reserved.
*/

var Component = new Brick.Component();
Component.entryPoint = function(){
	
	if (Brick.Permission.check('bostick', '30') != 1){ return; }
	
	var os = Brick.mod.bos;
	os.ApplicationManager.startupAfterRegister(function(){
		var wks = Brick.mod.bos.Workspace.instance;
		Brick.f('bostick', 'board', 'showIconWidget', wks.labelListWidget.element);
	});
	
};
