/*
	Copyright (c) 2004-2009, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.plugins.drawing.Grid"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojox.drawing.plugins.drawing.Grid"] = true;
dojo.provide("dojox.drawing.plugins.drawing.Grid");
dojo.require("dojox.drawing.plugins._Plugin");

dojox.drawing.plugins.drawing.Grid = dojox.drawing.util.oo.declare(
	// summary:
	//		Plugin that displays a grid on the Drawing canvas.
	// example:
	//		|	<div dojoType="dojox.drawing.Drawing" id="drawingNode"
	//		|		plugins="[{'name':'dojox.drawing.plugins.drawing.Grid', 'options':{gap:50}}]">
	//		|	</div>
	//
	dojox.drawing.plugins._Plugin,
	function(options){
		if(options.gap){
			this.major = options.gap;
		}
		this.setGrid();
		dojo.connect(this.canvas, "setZoom", this, "setZoom");
	},
	{
		type:"dojox.drawing.plugins.drawing.Grid",
		//
		// gap: Number
		//		How far apart to set the grid lines
		gap:100,
		major:100,
		minor:0,
		//
		// zoom: [readonly] Number
		//		The current zoom of the grid
		zoom:1,
		
		setZoom: function(zoom){
			// summary:
			//		Set's the zoom of the canvas
			this.zoom = zoom;
			this.setGrid();
		},
		setGrid: function(options){
			// summary:
			//		Renders grid
			//
			// TODO: major minor lines
			//	minors dont show on zoom out
			//	draw minors first
			//
			var mjr = Math.floor(this.major * this.zoom);
			var mnr = this.minor ? Math.floor(this.minor * this.zoom) : mjr;
			
			this.grid && this.grid.removeShape();
			
			var x1,x2,y1,y2,i,clr;
			var s = this.canvas.underlay.createGroup();
			var w = 2000;//this.canvas.width;
			var h = 1000;//this.canvas.height;
			var b = 1;
			var mj = "#00ffff";
			var mn = "#d7ffff";
			
			var createGridLine = function(x1,y1,x2,y2, c){
				s.createLine({x1: x1, y1: y1, x2: x2, y2: y2}).setStroke({style: "Solid", width: b, cap: "round", color:c});
			}
			
			// horz
			for(i=1,len = h/mnr; i<len; i++){
				x1 = 0, x2 = w;
				y1 = mnr*i, y2 = y1;
				
				
				clr = y1%mjr ? mn : mj;
				createGridLine(x1,y1,x2,y2, clr);
			}
			// vert
			for(i=1,len = w/mnr; i<len; i++){
				y1 = 0, y2 = h;
				x1 = mnr*i, x2 = x1;
				clr = x1%mjr ? mn : mj;
				createGridLine(x1,y1,x2,y2, clr);
			}	
		
			s.moveToBack();
			this.grid = s;
			this.util.attr(s, "id", "grid");
			return s;
		}
	}
);

}
