/** Library for grade item editor
*/

/** Defines a base class
*/
PartBase.prototype = {
	id: "base-name",
	tabLabel: "Base",
	
	//will be generated
	partType:'base',
	
	form: $('<fieldset>'),
	
	createDisplayElement:function(part) {
		return $('<li>').html(part.value);
	},
	
	// 
	removePart: function(part) {
		
	}
};

function PartBase() {

};

IdNumberPart.prototype = new PartBase();
function IdNumberPart() {
	var obj = this;
	
	this.id = "idnumber-part";
	this.tabLabel = "Class";
	this.partType= 'course';
	this.form = $('<fieldset><select class="course-select"></select><input type="submit" value="Add"></input></fieldset>');
	this.form.attr('id',this.id);
	this.select = obj.form.find('select');
	this.course = [];
	this.setClass = function(course) {
		var obj = this;
		this.course = course;
		
	};
	this.form.find('input:submit').click(function(e) {
		e.preventDefault();
	});
}


function GradeEditorEngine(){}

GradeEditorEngine.prototype = {
	name: "GradeEditorEngine",
	
	builders:[],
	
	moodleSessKey:null,
	
	setMoodleSessKey: function(val) {
		this.moodleSessKey = val;
	},
	
	moodleWwwRoot:null,
	
	setMoodleWwwRoot: function(val) {
		this.moodleWwwRoot = val;
	},
	
	initFormula:[],
	setInitFormula: function(formula) {
		this.initFormula = formula;
	},
	
	include: [],
	
	tabListElement: null,
	
	setTabListElement: function(element) {
		this.tabListElement = element;
	},
	
	formulaListElement: null,
	
	setFormulaListElement: function(element) {
		this.formulaListElement = element;
	},
	
	formElement: null,
	
	setFormElement: function(element) {
		this.formElement = element;
	},
	
	addPartBuilder: function(builder) {
		this.builders.push(builder);
		builder.engine = this;
	},
	
	run: function() {
		var obj = this;
		
	},
	
	// adds a new part to the formula
	addToFormula: function(part) {
	
	},
	
	insertPartAt: function(part,position) {
	
	},
	
	//need to save this back to the php back end!
	saveFormula: function(e) {
		
	}
	
	
};