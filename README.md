# WP Widget Base
==============
A simple class that allows you to quickly build custom widgets for your WordPress sites.

## Installation

Simply include the file somewhere in your WordPress theme. At Fingerpaint, we start many of our site builds using the [Roots Theme](https://github.com/roots/roots), and we include this class (and others) within `/lib/classes/`.

## How To Use 

To use the widget base, create a new file (e.g. `class.my_widget.php`) and include the widget base at the beginning of the class by using `require_once 'path/to/class.widget_base.php'`. 

Give your widget a name, and be sure to extend the widget base using something like the following format:

```
class My_Widget extends Widget_Base
{
    /**
     * Constructor function. Registers the widget and defines fields.
     *
     * @access public
     * @return My_Widget
     */
    public function __construct()
    {
    	// define the fields here 
    	// (options listed below)
        $this->fields = array();
		
		/* calls the Widget_Base's constructor.
		*  @param1 - Widget Slug
		*  @param2 - Widget Display Title
		*  @param3 - Widget Description
		* 
		*  Note: these should be unique for every widget your create
		*/
        parent::__construct(
            'my_widget',
            'My Widget',
            'My widget does this stuff.'
        );
    }
    
     /**
     * A function to display the widget contents on the front-end.
     *
     * @param array $args     The widget area arguments.
     * @param array $instance Data on this instance as reported by WP.
     * 
     * @access public
     * @return void
     */
    public function widget($args, $instance)
    {
    	// widget display logic goes here
    }
}

```

### Field Options

You can use many types of fields within your widgets. Fields are an array within your widget, and each field is an array with some options. For the most part, you can generate a field using the following format:

```
$this->fields = array(
			// the name of your field goes here
            'title' => array(
            	// the type of the field goes here
                'type' => 'text',
            ),
        );
```
However, some fields, such as Checkboxes and Select menus accept arrays so you can autopopulate them with other data (e.g. a list of users or categories). The format is slightly different, and looks like this:

```
$this->fields = array(
			// the name of your field goes here
            'title' => array(
            	// the type of the field goes here
                'type' => 'checkbox',
               // give it an array of values
                'values' => array (
                	'checkbox-value' => 'Checkbox Label'
                	'another-checkbox' => 'Another Label'
                )
            ),
        );
```


### Available field types
* Checkbox
* File
* Select
* Text
* Textarea 


## Changelog
============

#### 1.0.1
* Adding support for arrays of checkboxes
* Updating README with a very basic guide on how to use the class

#### 1.0.0
* Initial release