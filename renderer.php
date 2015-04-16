<?php

/**
 * This file contains a renderer for the scheduler module
 *
 * @package    mod
 * @subpackage scheduler
 * @copyright  2014 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the scheduler module.
 *
*/
class mod_scheduler_renderer extends plugin_renderer_base {

	/**
	 * Format a date in the current user's timezone.
	 * @param int $date a timestamp
	 * @return string printable date
 	 */
    public static function userdate($date) {
        if ($date == 0) {
            return '';
        } else {
            return userdate($date, get_string('strftimedaydate'));
        }
    }

    /**
     * Format a time in the current user's timezone.
     * @param int $date a timestamp
     * @return string printable time
     */
    public static function usertime($date) {
        if ($date == 0) {
            return '';
        } else {
            $timeformat = get_user_preferences('calendar_timeformat'); // Get user config.
            if (empty($timeformat)) {
                $timeformat = get_config(null, 'calendar_site_timeformat'); // Get calendar config if above not exist.
            }
            if (empty($timeformat)) {
                $timeformat = get_string('strftimetime'); //Get locale default format if both of the above do not exist.
            }
            return userdate($date, $timeformat);
        }
    }


    /**
     * Formats a grade in a specific scheduler for display
     * @param scheduler_instance $scheduler
     * @param string $grade the grade to be displayed
     * @param boolean $short formats the grade in short form (result empty if grading is
     * not used, or no grade is available; parantheses are put around the grade if it is present)
     * @return string the formatted grade
     */
    public function format_grade($scheduler, $grade, $short = false) {

        $result = '';
        if ($scheduler->scale == 0 || is_null($grade) ) {
            // Scheduler doesn't allow grading, or no grade entered.
            if (!$short) {
                $result = get_string('nograde');
            }
        } else {
            if ($scheduler->scale > 0) {
                // numeric grades
                $result .= $grade;
                if (strlen($grade) > 0) {
                    $result .= '/' . $scheduler->scale;
                }
            } else {
                // grade on scale
                if ($grade > 0) {
                    $result .= $scheduler->get_scale_levels()[$grade];
                }
            }
            if ($short && (strlen($result) > 0)) {
                $result = '('.$result.')';
            }
        }
        return $result;
    }

    /**
     * A utility function for producing grading lists (for use in formslib)
     *
     * Note that the selection list will contain a "nothing selected" option
     * with key -1 which will be displayed as "No grade".
     *
     * @param reference $scheduler
     * @return array the choices to be displayed in a grade chooser
     */
    public function grading_choices($scheduler) {
        if ($scheduler->scale > 0) {
            $scalegrades = array();
            for ($i = 0; $i <= $scheduler->scale; $i++) {
                $scalegrades[$i] = $i;
            }
        } else {
            $scaleid = - ($scheduler->scale);
            $scalegrades = $scheduler->get_scale_levels();
        }
        $scalegrades = array(-1 => get_string('nograde')) + $scalegrades;
        return $scalegrades;
    }

    public function user_profile_link(scheduler_instance $scheduler, stdClass $user) {
        $profileurl = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $scheduler->course));
        return html_writer::link($profileurl, fullname($user));
    }

    public function appointment_link($scheduler, $user, $appointmentid) {
        $paras = array(
                        'what' => 'viewstudent',
                        'id' => $scheduler->cmid,
                        'appointmentid' => $appointmentid
        );
        $url = new moodle_url('/mod/scheduler/view.php', $paras);
        return html_writer::link($url, fullname($user));
    }

    public function mod_intro($scheduler) {
        $o = $this->heading(format_string($scheduler->name), 2);

        if (trim(strip_tags($scheduler->intro))) {
            $o .= $this->box_start('mod_introbox');
            $o .= format_module_intro('scheduler', $scheduler->get_data(), $scheduler->cmid);
            $o .= $this->box_end();
        }
        return $o;
    }

    private function teacherview_tab(moodle_url $baseurl, $namekey, $what, $subpage = '', $nameargs = null) {
        $taburl = new moodle_url($baseurl, array('what' => $what, 'subpage' => $subpage));
        $tabname = get_string($namekey, 'scheduler', $nameargs);
        $id = ($subpage != '') ? $subpage : $what;
        $tab = new tabobject($id, $taburl, $tabname);
        return $tab;
    }

    public function teacherview_tabs(scheduler_instance $scheduler, moodle_url $baseurl, $selected, $inactive = null) {

        $statstab = $this->teacherview_tab($baseurl, 'statistics', 'viewstatistics', 'overall');
        $statstab->subtree = array(
                        $this->teacherview_tab($baseurl, 'overall', 'viewstatistics', 'overall'),
                        $this->teacherview_tab($baseurl, 'studentbreakdown', 'viewstatistics', 'studentbreakdown'),
                        $this->teacherview_tab($baseurl, 'staffbreakdown', 'viewstatistics', 'staffbreakdown', $scheduler->get_teacher_name()),
                        $this->teacherview_tab($baseurl, 'lengthbreakdown', 'viewstatistics', 'lengthbreakdown'),
                        $this->teacherview_tab($baseurl, 'groupbreakdown', 'viewstatistics', 'groupbreakdown')
        );

        $level1 = array(
                        $this->teacherview_tab($baseurl, 'myappointments', 'view', 'myappointments'),
                        $this->teacherview_tab($baseurl, 'allappointments', 'view', 'allappointments'),
                        $this->teacherview_tab($baseurl, 'datelist', 'datelist'),
                        $statstab,
                        $this->teacherview_tab($baseurl, 'downloads', 'downloads')
        );

        return $this->tabtree($level1, $selected, $inactive);
    }

    public function action_message($message, $type = 'success') {
        $classes = 'actionmessage '.$type;
        echo html_writer::div($message, $classes);
    }

    /**
     * Rendering a table of slots
     *
     * @param scheduler_slot_table $slottable the table to rended
     * @return string the HTML output
     */
    public function render_scheduler_slot_table(scheduler_slot_table $slottable) {
        $table = new html_table();

        $table->head  = array(get_string('date', 'scheduler'),
                        $slottable->scheduler->get_teacher_name(),
                        get_string('location', 'scheduler'),
                        get_string('comments', 'scheduler'));
        $table->align = array('left', 'left', 'left', 'left');

        if ($slottable->showgrades) {
            $table->head[] = get_string('grade', 'scheduler');
            $table->align[] = 'left';
        }

        $table->data = array();

        $previousdate = '';
        $previoustime = '';
        $previousendtime = '';

        foreach ($slottable->slots as $slot) {
            $rowdata = array();

            $startdate = $this->userdate($slot->starttime);
            $starttime = $this->usertime($slot->starttime);
            $endtime   = $this->usertime($slot->endtime);
            // Simplify display of dates, start and end times.
            if ($startdate == $previousdate && $starttime == $previoustime && $endtime == $previousendtime) {
                // If this row exactly matches previous, there's nothing to display.
                $startdatestr = '';
                $starttimestr = '';
                $endtimestr = '';
            } else if ($startdate == $previousdate) {
                // If this date matches previous date, just display times.
                $startdatestr = '';
                $starttimestr = $starttime;
                $endtimestr = $endtime;
            } else {
                // Otherwise, display all elements.
                $startdatestr = $startdate;
                $starttimestr = $starttime;
                $endtimestr = $endtime;
            }

            $timedata = html_writer::div($startdatestr, 'datelabel attended');
            $timedata .= html_writer::div("[$starttimestr - $endtimestr]", 'timelabel');

            $rowdata[] = $timedata;

            $rowdata[] = $this->user_profile_link($slottable->scheduler, $slot->teacher);
            $rowdata[] = format_string($slot->location);

            $studentnotes1 = '';
            $studentnotes2 = '';
            $textoptions = array('context' => $slottable->scheduler->context);
            if ($slot->slotnotes != '') {
                $studentnotes1 = html_writer::tag('strong', get_string('yourslotnotes', 'scheduler'));
                $studentnotes1 .= html_writer::empty_tag('br');
                $studentnotes1 .= format_text($slot->slotnotes, $slot->slotnotesformat, $textoptions);
                $studentnotes1 = html_writer::div($studentnotes1, 'slotnotes');
            }
            if ($slot->appointmentnotes != '') {
                $studentnotes2 = html_writer::tag('strong', get_string('yourappointmentnote', 'scheduler'));
                $studentnotes2 .= html_writer::empty_tag('br');
                $studentnotes2 .= format_text($slot->appointmentnotes, $slot->appointmentnotesformat, $textoptions);
                $studentnotes2 = html_writer::div($studentnotes2, 'appointmentnotes');
            }
            $studentnotes = $studentnotes1.$studentnotes2;

            $rowdata[] = $studentnotes;

            if ($slottable->showgrades) {
                if ($slot->otherstudents) {
                    $gradedata = $this->render($slot->otherstudents);
                } else {
                    $gradedata = $this->format_grade($slottable->scheduler, $slot->grade);
                }
                $rowdata[] = $gradedata;
            }

            $table->data[] = $rowdata;

            $previoustime = $starttime;
            $previousendtime = $endtime;
            $previousdate = $startdate;
        }

        return html_writer::table($table);
    }

    /**
     * Rendering a list of student, to be displayed within a larger table
     *
     * @param scheduler_slot_table $slottable the table to rended
     * @return string the HTML output
     */
    public function render_scheduler_student_list(scheduler_student_list $studentlist) {

        $o = '';

        $toggleid = html_writer::random_id('toggle');
        
        //$this->page->requires->yui_module('moodle-mod_scheduler-studentlist', 'M.mod_scheduler.studentlist.init'); //DELETE

        if ($studentlist->expandable && count($studentlist->students) > 0) {
            $this->page->requires->yui_module('moodle-mod_scheduler-studentlist',
                            'M.mod_scheduler.studentlist.init',
                            array($toggleid, (boolean) $studentlist->expanded) );
            $imgclass = 'studentlist-togglebutton';
            $alttext = get_string('showparticipants', 'scheduler');
            $o .= $this->output->pix_icon('t/switch', $alttext, 'moodle',
                            array('id' => $toggleid, 'class' => $imgclass));
        }

        $divprops = array('id' => 'list'.$toggleid);
        $o .= html_writer::start_div('studentlist', $divprops);
        if (count($studentlist->students) > 0) {
            $editable = $studentlist->actionurl && $studentlist->editable;
            if ($editable) {
                $o .= html_writer::start_tag('form', array('action' => $studentlist->actionurl,
                                'method' => 'post', 'class' => 'studentselectform'));
            }

            foreach ($studentlist->students as $student) {
                $class = 'otherstudent';
                $checkbox = '';
                if ($studentlist->checkboxname) {
                    if ($editable) {
                        $checkbox = html_writer::checkbox($studentlist->checkboxname, $student->entryid, $student->checked, '',
                                        array('class' => 'studentselect'));
                    } else {
                        $img = $student->checked ? 'ticked' : 'unticked';
                        $checkbox = $this->render(new pix_icon($img, '', 'scheduler', array('class' => 'statictickbox')));
                    }
                }
                if ($studentlist->linkappointment) {
                    $name = $this->appointment_link($studentlist->scheduler, $student->user, $student->entryid);
                } else {
                    $name = fullname($student->user);
                }
                if ($student->highlight) {
                    $class .= ' highlight';
                }
                $picture = $this->user_picture($student->user, array('courseid' => $studentlist->scheduler->courseid));
                $grade = '';
                if ($studentlist->showgrades && $student->grade) {
                    $grade = $this->format_grade($studentlist->scheduler, $student->grade, true);
                }
                $o .= html_writer::div($checkbox.$picture.' '.$name.' '.$grade, $class);
            }

            if ($editable) {
                $o .= html_writer::empty_tag('input', array(
                                'type' => 'submit',
                                'class' => 'studentselectsubmit',
                                'value' => $studentlist->buttontext
                ));
                $o .= html_writer::end_tag('form');
            }
        }
        $o .= html_writer::end_div();

        return $o;
    }

    public function render_scheduler_slot_booker(scheduler_slot_booker $booker) {

        
        $controls = '';
        if (count($booker->groupchoice) > 0) {
            $controls .= get_string('appointfor', 'scheduler');
            $choices = $booker->groupchoice;
            $choices[0] = get_string('appointsolo', 'scheduler');
            ksort($choices);
            $controls .= html_writer::select($choices, 'appointgroup', '', '');
            $controls .= $this->help_icon('appointagroup', 'scheduler');
            $controls .= ' ';
        }
        $controls .= html_writer::empty_tag('input', array('type' => 'submit',
                        'class' => 'bookerbutton', 'name' => 'savechoice',
                        'value' => get_string('savechoice', 'scheduler')));
        $controls .= ' ';
        if ($booker->candisengage) {
            $disengagelink = new moodle_url('/mod/scheduler/view.php',
                              array('what' => 'disengage',
                                            'id' => $booker->scheduler->cmid,
                                            'sesskey' => sesskey() ));
            $controls .= "<p>" . $this->action_link($disengagelink, get_string('disengage', 'scheduler')) . "</p>";
        }
        
        if ($booker->style == 'multi') {
            $o .= html_writer::div($controls, 'bookercontrols');
        }

        
        $table = new html_table();
        $table->head  = array( get_string('date', 'scheduler'), get_string('start', 'scheduler'),
                        get_string('end', 'scheduler'), get_string('location', 'scheduler'),
                        get_string('comments', 'scheduler'), get_string('choice', 'scheduler'),
                        s($booker->scheduler->get_teacher_name()),
                        get_string('groupsession', 'scheduler'));
        $table->align = array ('left', 'left', 'left', 'left', 'left', 'center', 'left', 'left');
        $table->id = 'slotbookertable';
        $table->data = array();

        $previousdate = '';
        $previoustime = '';
        $previousendtime = '';
        $canappoint = false;

        $index = 1;
        
        

        foreach ($booker->slots as $slot) {

            $rowdata = array();

            $startdate = $this->userdate($slot->starttime);
            $starttime = $this->usertime($slot->starttime);
            $endtime = $this->usertime($slot->endtime);
            // Simplify display of dates, start and end times.
            if ($startdate == $previousdate && $starttime == $previoustime && $endtime == $previousendtime) {
                // If this row exactly matches previous, there's nothing to display.
                $startdatestr = '';
                $starttimestr = '';
                $endtimestr = '';
            } else if ($startdate == $previousdate) {
                // If this date matches previous date, just display times.
                $startdatestr = '';
                $starttimestr = $starttime;
                $endtimestr = $endtime;
            } else {
                // Otherwise, display all elements.
                $startdatestr = $startdate;
                $starttimestr = $starttime;
                $endtimestr = $endtime;
            }

            $rowdata[] = $startdatestr;
            $rowdata[] = $starttimestr;
            $rowdata[] = $endtimestr;

            $rowdata[] = format_string($slot->location);

            $textoptions = array('context' => $booker->scheduler->context);
            $rowdata[] = format_text($slot->notes, $slot->notesformat, $textoptions);

            if ($booker->style == 'multi') {
                $inputname = "slotcheck[{$slot->slotid}]";
                $inputelm = html_writer::checkbox($inputname, $slot->slotid, $slot->bookedbyme, '', array('class' => 'slotbox'));
            } else {
                $inputparms = array('type' => 'radio', 'name' => 'slotid', 'value' => $slot->slotid, 
                  'onclick' => 'if(document.getElementsByClassName("shown").length > 0)  
                  {document.getElementsByClassName("shown")[0].className = "hidden"}; 
                   document.getElementById("save'.$index.'").parentElement.parentElement.className = "shown";
                   document.getElementById("save'.$index.'").parentElement.colSpan = "7"');
                   //document.getElementById("save'.$index.'").parentElement.style.textAlign = "right";');
                                    
//                'onclick' => 'document.getElementById("save'.$index.'").parentElement.parentElement.className = ""', 
//                'onblur' => 'document.getElementById("save'.$index.'").parentElement.parentElement.className = "hidden"');
                if ($slot->bookedbyme) {
                    $inputparms['checked'] = 1;
                }
                $inputelm = html_writer::empty_tag('input', $inputparms);
            }

            $groupinfo = $slot->bookedbyme ? get_string('complete', 'scheduler') : $slot->groupinfo;
            if ($slot->otherstudents) {
                $groupinfo .= $this->render($slot->otherstudents);
            }

            $rowdata[] = $inputelm;

            $rowdata[] = $this->user_profile_link($booker->scheduler, $slot->teacher);
            $rowdata[] = $groupinfo;

            $rowclass = ($slot->bookedbyme) ? 'booked' : 'bookable';

            $table->data[] = $rowdata;
            $table->rowclasses[] = $rowclass;

            $previoustime = $starttime;
            $previousendtime = $endtime;
            $previousdate = $startdate;


            $blankRowData[] = format_string($slot->location);
            
            
            
        $controls = '';
        if (count($booker->groupchoice) > 0) {
            $isGroupChoice = true;
            $controls .= get_string('appointfor', 'scheduler');
            $choices = $booker->groupchoice;
            $choices[0] = get_string('appointsolo', 'scheduler');
            ksort($choices);
            $controls .= html_writer::select($choices, 'appointgroup', '', '');
            $controls .= $this->help_icon('appointagroup', 'scheduler');
            $controls .= ' ';
        }
        else
        {
            $isGroupChoice = false;
        }
        if ($booker->candisengage) {
            $canDisengage = true;
            $disengagelink = new moodle_url('/mod/scheduler/view.php',
                              array('what' => 'disengage',
                                            'id' => $booker->scheduler->cmid,
                                            'sesskey' => sesskey() ));
        }
        else
        {
            $canDisengage = false;
        }

        /* some hacky shit */
        if($isGroupChoice && $canDisengage)
        {
            $controls .= html_writer::empty_tag('input', array('type' => 'submit',
                        'class' => 'bookerbutton', 'name' => 'savechoice',
                        'onclick' => 'alert("You are registering an appointment for your group. If any members of this group have a conflicting or additional appointment where multiple appointments are not allowed, these appointments will automatically be dropped.")',
                        'value' => get_string('savechoice', 'scheduler')));
            $controls .= ' ';
            $controls .= "<p>" . $this->action_link($disengagelink, get_string('disengage', 'scheduler')) . "</p>";
        }
        elseif ($canDisengage) 
        {
            $controls .= html_writer::empty_tag('input', array('type' => 'submit',
                        'class' => 'bookerbutton', 'name' => 'savechoice',
                        'value' => get_string('savechoice', 'scheduler')));
            $controls .= ' ';
            $controls .= "<p>" . $this->action_link($disengagelink, get_string('disengage', 'scheduler')) . "</p>";
        }
        else
        {
            $controls .= html_writer::empty_tag('input', array('type' => 'submit',
                        'class' => 'bookerbutton', 'name' => 'savechoice',
                        'value' => get_string('savechoice', 'scheduler')));
        }



//            $blankRowData[] = "";
            $blankRowData[] = "";
            $blankRowData[] = "";
            $blankRowData[] = "";
            $blankRowData[] = "";
//            $blankRowData[] = "";
//            $blankRowData[] = html_writer::empty_tag('input', array('type' => 'submit', 'class' => 'bookerbutton', 'id' => 'save'.$index++, 'name' => 'savechoice', 'rowspan' => '7', 'value' => get_string('savechoice', 'scheduler')));
              $blankRowData[] = html_writer::div($controls, 'bookercontrols', array('id' => 'save'.$index++));
//            $blankRowData[] = "";
//            $blankRowData[] = "";

            $table->data[] = $blankRowData;
            $table->rowclasses[] = "hidden";

            unset($blankRowData);
        }

        if ($booker->style == 'multi' && $booker->maxselect > 0) {
            $this->page->requires->yui_module('moodle-mod_scheduler-limitchoices',
                            'M.mod_scheduler.limitchoices.init', array($booker->maxselect) );
        }

        
//        $controls = '';
//        if (count($booker->groupchoice) > 0) {
//            $controls .= get_string('appointfor', 'scheduler');
//            $choices = $booker->groupchoice;
//            $choices[0] = get_string('appointsolo', 'scheduler');
//            ksort($choices);
//            $controls .= html_writer::select($choices, 'appointgroup', '', '');
//            $controls .= $this->help_icon('appointagroup', 'scheduler');
//            $controls .= ' ';
//        }
//        $controls .= html_writer::empty_tag('input', array('type' => 'submit',
//                        'class' => 'bookerbutton', 'name' => 'savechoice',
//                        'value' => get_string('savechoice', 'scheduler')));
//        $controls .= ' ';
//        if ($booker->candisengage) {
//            $disengagelink = new moodle_url('/mod/scheduler/view.php',
//                              array('what' => 'disengage',
//                                            'id' => $booker->scheduler->cmid,
//                                            'sesskey' => sesskey() ));
//            $controls .= $this->action_link($disengagelink, get_string('disengage', 'scheduler'));
//        }

        $o = '';
        $o .= html_writer::start_tag('form', array('action' => $booker->actionurl,
                        'method' => 'post', 'class' => 'bookerform'));

        $o .= html_writer::input_hidden_params($booker->actionurl);

        $o .= html_writer::table($table);

        if ($booker->style == 'multi') {
            $o .= html_writer::div($controls, 'bookercontrols');
        }

        $o .= html_writer::end_tag('form');

        return $o;
    }

    public function render_scheduler_command_bar(scheduler_command_bar $commandbar) {
        $o = '';
        foreach ($commandbar->linkactions as $id => $action) {
            $this->add_action_handler($action, $id);
        }
        $o .= html_writer::start_div('commandbar');
        if ($commandbar->title) {
            $o .= html_writer::span($commandbar->title, 'title');
        }
        foreach ($commandbar->menus as $m) {
            $o .= $this->render($m);
        }
        $o .= html_writer::end_div();
        return $o;
    }

    public function render_scheduler_slot_manager(scheduler_slot_manager $slotman) {

        $this->page->requires->yui_module('moodle-mod_scheduler-saveseen',
                        'M.mod_scheduler.saveseen.init', array($slotman->scheduler->cmid) );

        $o = '';

        $table = new html_table();
        $table->head  = array('', get_string('date', 'scheduler'), get_string('start', 'scheduler'),
                        get_string('end', 'scheduler'), get_string('students', 'scheduler') );
        $table->align = array ('center', 'left', 'left', 'left', 'left');
        if ($slotman->showteacher) {
            $table->head[] = s($slotman->scheduler->get_teacher_name());
            $table->align[] = 'left';
        }
        $table->head[] = get_string('action', 'scheduler');
        $table->align[] = 'center';

        $table->id = 'slotmanager';
        $table->data = array();

        $previousdate = '';
        $previoustime = '';
        $previousendtime = '';

        foreach ($slotman->slots as $slot) {

            $rowdata = array();

            $selectbox = html_writer::checkbox('selectedslot[]', $slot->slotid, false, '', array('class' => 'slotselect'));
            $rowdata[] = $slot->editable ? $selectbox : '';

            $startdate = $this->userdate($slot->starttime);
            $starttime = $this->usertime($slot->starttime);
            $endtime = $this->usertime($slot->endtime);
            // Simplify display of dates, start and end times.
            if ($startdate == $previousdate && $starttime == $previoustime && $endtime == $previousendtime) {
                // If this row exactly matches previous, there's nothing to display.
                $startdatestr = '';
                $starttimestr = '';
                $endtimestr = '';
            } else if ($startdate == $previousdate) {
                // If this date matches previous date, just display times.
                $startdatestr = '';
                $starttimestr = $starttime;
                $endtimestr = $endtime;
            } else {
                // Otherwise, display all elements.
                $startdatestr = $startdate;
                $starttimestr = $starttime;
                $endtimestr = $endtime;
            }

            $rowdata[] = $startdatestr;
            $rowdata[] = $starttimestr;
            $rowdata[] = $endtimestr;

            $rowdata[] = $this->render($slot->students);

            if ($slotman->showteacher) {
                $rowdata[] = $this->user_profile_link($slotman->scheduler, $slot->teacher);
            }

            $actions = '';
            if ($slot->editable) {
                $url = new moodle_url($slotman->actionurl, array('what' => 'deleteslot', 'slotid' => $slot->slotid));
                $actions .= $this->action_icon($url, new pix_icon('t/delete', get_string('delete')));

                $url = new moodle_url($slotman->actionurl, array('what' => 'updateslot', 'slotid' => $slot->slotid));
                $actions .= $this->action_icon($url, new pix_icon('t/edit', get_string('edit')));
            }

            if ($slot->isattended || $slot->isappointed > 1) {
                $groupicon = 'i/groupevent';
            } else if ($slot->exclusivity == 1) {
                $groupicon = 't/groupn';
            } else {
                $groupicon = 't/groupv';
            }
            $groupalt = ''; $groupact = null;
            if ($slot->isattended) {
                $groupalt = 'attended';
            } else if ($slot->isappointed > 1) {
                $groupalt = 'isnonexclusive';
            } else if ($slot->editable) {
                if ($slot->exclusivity == 1) {
                    $groupact = array('what' => 'allowgroup', 'slotid' => $slot->slotid);
                    $groupalt = 'allowgroup';
                } else {
                    $groupact = array('what' => 'forbidgroup', 'slotid' => $slot->slotid);
                    $groupalt = 'forbidgroup';
                }
            } else {
                if ($slot->exclusivity == 1) {
                    $groupalt = 'allowgroup';
                } else {
                    $groupalt = 'forbidgroup';
                }
            }
            if ($groupact) {
                $url = new moodle_url($slotman->actionurl, $groupact);
                $actions .= $this->action_icon($url, new pix_icon($groupicon, get_string($groupalt, 'scheduler')));
            } else {
                $actions .= $this->pix_icon($groupicon, get_string($groupalt, 'scheduler'));
            }

            if ($slot->editable && $slot->isappointed) {
                $url = new moodle_url($slotman->actionurl, array('what' => 'revokeall', 'slotid' => $slot->slotid));
                $actions .= $this->action_icon($url, new pix_icon('s/no', get_string('revoke', 'scheduler')));
            }

            if ($slot->exclusivity > 1) {
                $actions .= ' ('.$slot->exclusivity.')';
            }
            $rowdata[] = $actions;

            $table->data[] = $rowdata;

            $previoustime = $starttime;
            $previousendtime = $endtime;
            $previousdate = $startdate;
        }
        $o .= html_writer::table($table);

        return $o;
    }

    public function render_scheduler_scheduling_list(scheduler_scheduling_list $list) {

        $mtable = new html_table();

        $mtable->head  = array ('', get_string('name'));
        $mtable->align = array ('center', 'left');
        foreach ($list->extraheaders as $field) {
            $mtable->head[] = $field;
            $mtable->align[] = 'left';
        }
        $mtable->head[] = get_string('action', 'scheduler');
        $mtable->align[] = 'center';

        $mtable->data = array();
        foreach ($list->lines as $line) {
            $data = array($line->pix, $line->name);
            foreach ($line->extrafields as $field) {
                $data[] = $field;
            }
            $actions = '';
            if ($line->actions) {
                $menu = new action_menu($line->actions);
                $menu->actiontext = get_string('schedule', 'scheduler');
                $actions = $this->render($menu);
            }
            $data[] = $actions;
            $mtable->data[] = $data;
        }
        return html_writer::table($mtable);
    }

}
