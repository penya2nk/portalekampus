var Modal = {};
Modal.Box = Class.create();
Object.extend(Modal.Box.prototype, {
	initialize: function(id) {
		this.createOverlay();

		this.modal_box = $(id);
		this.modal_box.show = this.show.bind(this);
		this.modal_box.hide = this.hide.bind(this);
        this.parent_element = this.modal_box.parentNode;  // add this line, so parent_element will be initilized
	},

	createOverlay: function() {
		if($('modal_overlay')) {
			this.overlay = $('modal_overlay');
		} else {
			this.overlay = document.createElement('div');
			this.overlay.id = 'modal_overlay';
			Object.extend(this.overlay.style, {
				position: 'absolute',
				top: 0,
				left: 0,
				zIndex: 90,
				width: '100%',
				backgroundColor: '#000',
				display: 'none'
			});
         this.overlay = document.forms[0].insert(this.overlay); // create overlay inside form
         this.overlay = $('modal_overlay'); // look for new object
		}
	},

	moveModalBox: function(where) {
		Element.remove(this.modal_box);
		if(where == 'back')
            this.modal_box = this.parent_element.appendChild(this.modal_box);   
		else
            this.modal_box = this.overlay.parentNode.insertBefore(this.modal_box, this.overlay);
	},

	show: function() {
	    
	    // center the div
	    this.parent_element = this.modal_box.parentNode;	
        
		this.moveModalBox('out');
		//this.overlay.onclick = this.hide.bind(this);
		this.selectBoxes('hide');
		new Effect.Appear(this.overlay, {duration: 0.1, from: 0.0, to: 0.3});
		this.modal_box.style.display = '';
	},

	hide: function() {
		this.selectBoxes('show');
		new Effect.Fade(this.overlay, {duration: 0.1});
		this.modal_box.style.display = 'none';
		this.moveModalBox('back');
		$A(this.modal_box.getElementsByTagName('input')).each(function(e){if(e.type!='submit')e.value=''});
	},

	selectBoxes: function(what) {
        
	}
});