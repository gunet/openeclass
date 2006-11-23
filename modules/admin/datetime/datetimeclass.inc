<?
/*
 * Class to manipulate date and time on the form.
 * 
 * Author: Viatcheslav Ivanov, E-Witness Inc., Canada;
 * mail: ivanov@e-witness.ca, v_iv@hotmail.com;
 * web: www.e-witness.ca; www.coolwater.ca; www.strongpost.net;
 * version: 1.40 /03.14.2002
 *
 */
if ( !defined("DATETIMECLASS_INC") ) {

	define("DATETIMECLASS_INC",1);

	class DATETIME {
	    var $current_names = array( 'yearname' => 'year',
	    							'monthname' => 'month',
	    							'dayname' => 'day',
	    							'hourname' => 'hour',
	    							'ampmname' => 'ampm',
	    							'minutename' => 'minute',
	    							'secondname' => 'second' );
   		var $current_selected = array();

		function DATETIME() {
			// initialization
		}

		// set current name of select on the form
		// $keyname: "yearname", "monthname", "dayname", "hourname", "ampmname", "minutename", "secondname"
		function set_selectname($keyname, $new_name) {
			if($new_name) $this->current_names[$keyname] = $new_name;
		}

		// set current names of date selects (month, day, year) on the form (wrapper easy to use)
		function set_datename($new_name_month = "", $new_name_day = "", $new_name_year = "") {
			$this->set_selectname("monthname", $new_name_month);
			$this->set_selectname("dayname", $new_name_day);
			$this->set_selectname("yearname", $new_name_year);
		}

		// set current names of time selects (hour, minute, second, am-pm) on the form (wrapper easy to use)
		function set_timename($new_name_hour = "", $new_name_minute = "", $new_name_second = "", $new_name_ampm = "") {
			$this->set_selectname("hourname", $new_name_hour);
			$this->set_selectname("minutename", $new_name_minute);
			$this->set_selectname("secondname", $new_name_second);
			$this->set_selectname("ampmname", $new_name_ampm);
		}

		// set current names of date and time selects on the form (wrapper easy to use)
		function set_datetimename($new_name_month = "", $new_name_day = "", $new_name_year = "", $new_name_hour = "", $new_name_minute = "", $new_name_second = "", $new_name_ampm = "") {
			$this->set_datename($new_name_month, $new_name_day, $new_name_year);
			$this->set_timename($new_name_hour, $new_name_minute, $new_name_second, $new_name_ampm);
		}

		// set selected values by string variable date or(and) time format
		// use it for initialization by info from different sources (for example DB)
		// NOTE: it will return TRUE with date range "Dec 31 1969" and " Jan 18 2038", otherwise FALSE
		function set_datetime_byvar($datetimemix) {
			$timestamp = strtotime($datetimemix);
			if ($timestamp != -1) {
				$this->current_selected['yearname'] = date("Y", $timestamp);
				$this->current_selected['monthname'] = date("n", $timestamp);
				$this->current_selected['dayname'] = date("j", $timestamp);
				$this->current_selected['hourname'] = date("G", $timestamp); // keep internal 24 hour cycle
				$this->current_selected['minutename'] = (string)((int)date("i", $timestamp));
				$this->current_selected['secondname'] = (string)((int)date("s", $timestamp));
				return true;
			} else {
				return false;
			}
		}
		
		function set_datetime_byvar2($datetimemix) {
			$timestamp = $datetimemix;
			if ($timestamp != -1) {
				$this->current_selected['yearname'] = date("Y", $timestamp);
				$this->current_selected['monthname'] = date("n", $timestamp);
				$this->current_selected['dayname'] = date("j", $timestamp);
				$this->current_selected['hourname'] = date("G", $timestamp); // keep internal 24 hour cycle
				$this->current_selected['minutename'] = (string)((int)date("i", $timestamp));
				$this->current_selected['secondname'] = (string)((int)date("s", $timestamp));
				return true;
			} else {
				return false;
			}
		}

		// set selected values by components of date
		// use it for initialization when date is out of range "Dec 31 1969" and " Jan 18 2038"
		// and you can't use set_datetime_byvar() method
		function set_datetime_bycomponents($new_month = "", $new_day = "", $new_year = "", $new_hour = "", $new_minute = "", $new_second = "", $new_ampm = "") {
			if ($this->isit_numeric($new_year) && (int)$new_year > 0 && (int)$new_year <= 9999)
				$this->current_selected['yearname'] = $new_year;
			if ($this->isit_numeric($new_month) && (int)$new_month > 0 && (int)$new_month <= 12)
				$this->current_selected['monthname'] = $new_month;
			if ($this->isit_numeric($new_day) && (int)$new_day > 0 && (int)$new_day <= 31)
				$this->current_selected['dayname'] = $new_day;
			if ($new_ampm && (strtolower($new_ampm) == "am" || strtolower($new_ampm) == "pm"))
				$this->current_selected['ampmname'] = strtolower($new_ampm);
			if ($this->isit_numeric($new_hour) && (int)$new_hour >= 0 && (int)$new_hour < 24) {
				$this->current_selected['hourname'] = $new_hour;
				$this->current_selected['hourname'] = $this->hours12to24();
			}
			if ($this->isit_numeric($new_minute) && (int)$new_minute >= 0 && (int)$new_minute < 60)
				$this->current_selected['minutename'] = $new_minute;
			if ($this->isit_numeric($new_second) && (int)$new_second >= 0 && (int)$new_second < 60)
				$this->current_selected['secondname'] = $new_second;
		}

		// set selected values by global variables
		// $globalscope: "GLOBALS", "HTTP_POST_VARS", "HTTP_GET_VARS", "HTTP_COOKIE_VARS", "HTTP_SESSION_VARS"
		function set_datetime_byglobal($globalscope = "GLOBALS") {
			global $$globalscope;
			reset ($this->current_names);
			while (list($key,$val) = each($this->current_names))
				$this->current_selected[$key] = ${$globalscope}[$val];
			$this->current_selected['hourname'] = $this->hours12to24();
		}

		// return string with input type select
		function get_select($select_name, $select_array_values, $selected_value = "", $select_first_line = "", $select_style = "", $select_size = 1) {
			$str_select = "";
			$str_select.= "\n<select name=\"".$select_name."\" size=".$select_size;
			// kstratos - 5/11/2006 commit next 2 lines to avoid year,month, etc
			//$str_select.= ($select_style)? " ".$select_style.">\n" : ">\n";
			//$str_select.= ($select_first_line)? "<option value=\"\">".$select_first_line."\n" : "";
			while(list($key,$val) = each($select_array_values))
			{
				$str_select.= "<option value=\"".$key."\"";
				$str_select.= ($this->isit_numeric($selected_value) && $key == $selected_value) ? " SELECTED>" : ">";
				$str_select.= $val."\n";
			}
			$str_select.= "</select>\n";
			return $str_select;
		}

		// return select with years or text with selected year
		// year_syle may has values : ldigit (4 digit year), sdigit (2 last digit year)
		function get_select_years($year_style = "ldigit", $year_from = "2000", $year_to = "2029", $select_first_line = "", $select_style = "", $select_size = 1, $textonly = 0) {
			if ($year_to == "") $year_to = date("Y");
			if ($year_from == "") $year_from = $year_to - 10;
			if ($year_from <= $year_to)
				for ($i = $year_from; $i <= $year_to; $i++)
					$arr_years[$i] = ($year_style == "ldigit")? $i : substr($i, -2);
			else
				for ($i = $year_from; $i >= $year_to; $i--)
					$arr_years[$i] = ($year_style == "ldigit")? $i : substr($i, -2);
			if ($textonly)
				return $arr_years[$this->current_selected["yearname"]];
			else
				return $this->get_select($this->current_names["yearname"], $arr_years, $this->current_selected["yearname"], $select_first_line, $select_style, $select_size);
		}

		// return select with months or text with selected month
		// month_style may has values: digit (number of month), lword (3 letter abbreviation), sword (full word)
		function get_select_months($leading_zeros = 1, $month_style = "digit", $select_first_line = "", $select_style = "", $select_size = 1, $textonly = 0) {
			for ($i = 1; $i <= 12; $i++)
				if ($month_style == "lword")
					$arr_months[$i] = strftime("%B", mktime( 0,0,0,$i,1,2000 ));
				elseif ($month_style == "sword")
					$arr_months[$i] = strftime("%b", mktime( 0,0,0,$i,1,2000 ));
				else
					$arr_months[$i] = ($leading_zeros && $i < 10)? "0".$i : $i;
			if ($textonly)
				return $arr_months[$this->current_selected["monthname"]];
			else
				return $this->get_select($this->current_names["monthname"], $arr_months, $this->current_selected["monthname"], $select_first_line, $select_style, $select_size);
		}

		// return select with days or text with selected day
		function get_select_days($leading_zeros = 1, $select_first_line = "", $select_style = "", $select_size = 1, $textonly = 0) {
			for ($i = 1; $i <= 31; $i++)
				$arr_days[$i] = ($leading_zeros && $i < 10)? "0".$i : $i;
			if ($textonly)
				return $arr_days[$this->current_selected["dayname"]];
			else
				return $this->get_select($this->current_names["dayname"], $arr_days, $this->current_selected["dayname"], $select_first_line, $select_style, $select_size);
		}

		// return select with hours or text with selected hour
		// ONLY if use 12 hours cycle, use select am - pm !
		function get_select_hours($leading_zeros = 1, $hour_style = 24, $select_first_line = "", $select_style = "", $select_size = 1, $textonly = 0) {
			if ($hour_style == 12) {
				for ($i = 1; $i <= 12; $i++)
					$arr_hours[$i] = ($leading_zeros && $i < 10)? "0".$i : $i;
				if ($this->isit_numeric($this->current_selected["hourname"])) {
					if ($this->current_selected["hourname"] > 0 && $this->current_selected["hourname"] <= 12) {
						$temp_selected = $this->current_selected["hourname"];
						$this->current_selected["ampmname"] = ($this->current_selected["hourname"] == 12)? "pm" : "am";
					} elseif ($this->current_selected["hourname"] > 12) {
						$temp_selected = $this->current_selected["hourname"] - 12;
						$this->current_selected["ampmname"] = "pm";
					} else {
						$temp_selected = 12;
						$this->current_selected["ampmname"] = "am";
					}
				}
			} else {
				for ($i = 0; $i < 24; $i++)
					$arr_hours[$i] = ($leading_zeros && $i < 10)? "0".$i : $i;
				$temp_selected = $this->current_selected["hourname"];
			}
			if ($textonly)
				return $arr_hours[$temp_selected];
			else
				return $this->get_select($this->current_names["hourname"], $arr_hours, $temp_selected, $select_first_line, $select_style, $select_size);
		}

		// return select with am - pm options. Use ONLY with 12 hours cycle select
		function get_select_ampm($select_first_line = "", $select_style = "", $select_size = 1) {
			$str_select = "";
			$str_select.= "\n<select name=\"".$this->current_names["ampmname"]."\" size=".$select_size;
			$str_select.= ($select_style)? " style=\"".$select_style."\">\n" : ">\n";
			$str_select.= ($select_first_line)? "<option value=\"\">".$select_first_line."\n" : "";
			$str_select.= "<option value=\"am\"";
			$str_select.= ($this->current_selected["ampmname"] == "am") ? " SELECTED>am\n" : ">am\n";
			$str_select.= "<option value=\"pm\"";
			$str_select.= ($this->current_selected["ampmname"] == "pm") ? " SELECTED>pm\n" : ">pm\n";
			$str_select.= "</select>\n";
			return $str_select;
		}

		// return select with minutes or text with selected minute
		function get_select_minutes($leading_zeros = 1, $select_first_line = "", $select_style = "", $select_size = 1, $textonly = 0) {
			for ($i = 0; $i < 60; $i++)
				$arr_minutes[$i] = ($leading_zeros && $i < 10)? "0".$i : $i;
			if ($textonly)
				return $arr_minutes[$this->current_selected["minutename"]];
			else
				return $this->get_select($this->current_names["minutename"], $arr_minutes, $this->current_selected["minutename"], $select_first_line, $select_style, $select_size);
		}

		// return select with seconds or text with selected second
		function get_select_seconds($leading_zeros = 1, $select_first_line = "", $select_style = "", $select_size = 1, $textonly = 0) {
			for ($i = 0; $i < 60; $i++)
				$arr_seconds[$i] = ($leading_zeros && $i < 10)? "0".$i : $i;
			if ($textonly)
				return $arr_seconds[$this->current_selected["secondname"]];
			else
				return $this->get_select($this->current_names["secondname"], $arr_seconds, $this->current_selected["secondname"], $select_first_line, $select_style, $select_size);
		}

		// return date in format: month/day/year by information from the form
		function get_date_entered() {
			$str_ret = "";
			if (count($this->current_selected))
				if ($this->current_selected["monthname"] || $this->current_selected["dayname"] || $this->current_selected["yearname"]) {
					$str_ret = ($this->current_selected["monthname"])? $this->current_selected["monthname"]."/" : "__/";
					$str_ret.= ($this->current_selected["dayname"])? $this->current_selected["dayname"]."/" : "__/";
					$str_ret.= ($this->current_selected["yearname"])? $this->current_selected["yearname"] : "__";
				}
			return $str_ret;
		}

		// return time in format hour:minute:second by information from the form
		function get_time_entered() {
			$str_ret = "";
			if (count($this->current_selected))
				if ($this->isit_numeric($this->current_selected["hourname"]) || $this->isit_numeric($this->current_selected["minutename"]) || $this->isit_numeric($this->current_selected["secondname"])) {
					$str_ret = ($this->isit_numeric($this->current_selected["hourname"]))? $this->current_selected["hourname"].":" : "__:";
					$str_ret.= ($this->isit_numeric($this->current_selected["minutename"]))? $this->get_select_minutes(1, "", "", 1, 1) : "__";
					$str_ret.= ($this->isit_numeric($this->current_selected["secondname"]))? ":".$this->get_select_seconds(1, "", "", 1, 1) : "";
				}
			return $str_ret;
		}

		// return unix timestamp by information from the form
		// NOTE: it will return -1 if timestamp out of range "Dec 31 1969" and " Jan 18 2038"
		function get_timestamp_entered() {
			return mktime($this->current_selected["hourname"], $this->current_selected["minutename"], $this->current_selected["secondname"], $this->current_selected["monthname"], $this->current_selected["dayname"], $this->current_selected["yearname"]);
		}

		// return error message by date entered
		function get_date_error() {
			$err_msg = "";
			if (count($this->current_selected))
				if ($this->current_selected["yearname"] || $this->current_selected["monthname"] || $this->current_selected["dayname"])
					if ($this->current_selected["yearname"] && $this->current_selected["monthname"] && $this->current_selected["dayname"]) {
						if (!checkdate($this->current_selected["monthname"], $this->current_selected["dayname"], $this->current_selected["yearname"]))
							$err_msg = "Date ".$this->get_date_entered()." is incorrect";
					} else {
						$err_msg = "Date ".$this->get_date_entered()." is incomplete";
					}
			return $err_msg;
		}

		// return true if date entered is empty
		function is_date_empty() {
			$flag_ret = true;
			if (count($this->current_selected))
				if ($this->current_selected["yearname"] || $this->current_selected["monthname"] || $this->current_selected["dayname"])
					$flag_ret = false;
			return $flag_ret;
		}

		// private function to add to PHP3 is_numeric functionality
		function isit_numeric($anystring) {
			return (string)((int)$anystring) == (string)$anystring;
		}

		// private function correct time from 12 am-pm hours to 24 hours cycle
		function hours12to24() {
			if ($this->current_selected["ampmname"] == "pm") {
				if ($this->current_selected["hourname"] < 12) return 12 + $this->current_selected["hourname"];
				elseif ($this->current_selected["hourname"] >= 12) return $this->current_selected["hourname"];
			} elseif ($this->current_selected["ampmname"] == "am") {
				if ($this->current_selected["hourname"] < 12 || $this->current_selected["hourname"] > 12) return $this->current_selected["hourname"];
				elseif ($this->current_selected["hourname"] == 12) return 0;
			} else {
				return $this->current_selected["hourname"];
			}
		}

	}

} // if ( !defined("DATETIMECLASS_INC") )
?>
