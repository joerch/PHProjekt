/*
	Copyright (c) 2004-2009, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dijit.form.NumberSpinner"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dijit.form.NumberSpinner"] = true;
dojo.provide("dijit.form.NumberSpinner");

dojo.require("dijit.form._Spinner");
dojo.require("dijit.form.NumberTextBox");

dojo.declare("dijit.form.NumberSpinner",
	[dijit.form._Spinner, dijit.form.NumberTextBoxMixin],
	{
	// summary:
	//		Extends NumberTextBox to add up/down arrows and pageup/pagedown for incremental change to the value
	//
	// description:
	//		A `dijit.form.NumberTextBox` extension to provide keyboard accessible value selection
	//		as well as icons for spinning direction. When using the keyboard, the typematic rules
	//		apply, meaning holding the key will gradually increarease or decrease the value and
	// 		accelerate.
	//
	// example:
	//	| new dijit.form.NumberSpinner({ constraints:{ max:300, min:100 }}, "someInput");

	adjust: function(/* Object */val, /* Number*/delta){
		// summary:
		//		Change Number val by the given amount
		// tags:
		//		protected

		var tc = this.constraints,
			v = isNaN(val),
			gotMax = !isNaN(tc.max),
			gotMin = !isNaN(tc.min)
		;
		if(v && delta != 0){ // blank or invalid value and they want to spin, so create defaults
			val = (delta > 0) ?
				gotMin ? tc.min : gotMax ? tc.max : 0 :
				gotMax ? this.constraints.max : gotMin ? tc.min : 0
			;
		}
		var newval = val + delta;
		if(v || isNaN(newval)){ return val; }
		if(gotMax && (newval > tc.max)){
			newval = tc.max;
		}
		if(gotMin && (newval < tc.min)){
			newval = tc.min;
		}
		return newval;
	},

	_onKeyPress: function(e){
		if((e.charOrCode == dojo.keys.HOME || e.charOrCode == dojo.keys.END) && !(e.ctrlKey || e.altKey || e.metaKey)
		&& typeof this.attr('value') != 'undefined' /* gibberish, so HOME and END are default editing keys*/){
			var value = this.constraints[(e.charOrCode == dojo.keys.HOME ? "min" : "max")];
			if(value){
				this._setValueAttr(value,true);
			}
			// eat home or end key whether we change the value or not
			dojo.stopEvent(e);
		}
	}

});

}
