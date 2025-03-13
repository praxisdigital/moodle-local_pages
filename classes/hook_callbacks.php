<?php
namespace local_pages;

use html_writer;
use moodle_url;

/**
 * Hook callbacks for local_pages.
 *
 * @package    local_pages
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Add custom head elements.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     * @see https://github.com/praxisdigital/moodle-local_pages/blob/1cb17ca8f6fd5cb46f1faaacecda6dc84e1ed0dd/lib.php#L542-L580
     */
    public static function standard_head_html(\core\hook\output\before_standard_head_html_generation $hook): void {
        global $CFG, $DB, $PAGE, $SITE;

        if ($PAGE->pagetype !== 'local-pages-index') {
            return;
        }

        $pageid = optional_param('id', 0, PARAM_INT);
        $custompage = \local_pages\custompage::load($pageid);
        $output = get_config('local_pages', 'additionalhead') ? $custompage->meta : '';

        $query = "SELECT * FROM {files}
                  WHERE component = 'local_pages'
                  AND filearea = 'ogimage'
                  AND itemid = ?
                  AND filesize > 0";
        if ($filerecord = $DB->get_record_sql($query, [$custompage->id])) {
            $src = $CFG->wwwroot . '/pluginfile.php/1/local_pages/ogimage/' . $custompage->id . '/' . $filerecord->filename;
            $output .= "\n" . '    <meta property="og:image" content="' . $src . '" />';
        }

        $url = new moodle_url($PAGE->url);
        $url->remove_all_params();

        if (get_config('local_pages', 'cleanurl_enabled') && $pageid === 0) {
            $url = str_replace('index.php', '', $url->out());
            $url .= $custompage->menuname;
        } else {
            $url = $url->out() . '?id='. $custompage->id;
        }

        $output .= "\n" . '    <meta property="og:site_name" content="' . $SITE->fullname . '" />';
        $output .= "\n" . '    <meta property="og:type" content="website" />';
        $output .= "\n" . '    <meta property="og:title" content="' . $PAGE->title . '" />';
        $output .= "\n" . '    <meta property="og:url" content="' . $url . '" />';

        $hook->add_html($output);
    }
}