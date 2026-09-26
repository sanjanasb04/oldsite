=== LearnPress - H5P ===
Contributors: thimpress
Donate link:
Tags: elearning, education, course, lms, learning management system
Tested up to: 6.9
Stable tag: 4.0.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Changelog ==

= 4.0.9 (2026-03-13) =
~ Fixed: translation.

= 4.0.8 (2026-01-16) =
~ Optimize query list h5p on lp profile.

= 4.0.7 (2025-09-19) =
~ Fixed: compatible with PHP 8.4.
~ Fixed: calculate result.
~ Added: setting display "H5P button complete".

= 4.0.6 (2025-04-04) =
~ Fixed: API get h5p item, case: item selected not on items first.

= 4.0.5 (2025-03-20) =
~ Fixed minor bugs.

= 4.0.4 (2025-03-10) =
~ Refactor code.

= 4.0.3 (2023-12-26) =
~ Tweak: slug link, rewrite rule.
~ Tweak: lp_h5p_submenu_order function.
~ Fixed: evaluate wrong, reason by call $item['item_id'], $item is object LP_User_Item.

= 4.0.2 (2023-10-30) =
~ Fixed: minor bugs.
~ Tweak: slug link, rewrite rule.

= 4.0.2 (2022-11-23) =
~ Compatible PHP 8.1
~ Deprecated: __get on the LP_Assignment class.
~ Replace call array key ['items'] to get_items of LP_Query_List_Table.

= 4.0.1 =
- Fix Evaluate via results of the H5P when finish course.
- Call get_total_item_unassigned from LP.
- Remove results 0 when evaluate course.
- Fix add param 'pass' when evaluate course.
- Check get_course_data is false.
~ Check $course_data->get_item is false.
~ Added: show progress on single course.

= 4.0.0 =
- Compatible with LP4.

= 3.0.1 =
- Fix submit Answer Question in Video H5P.
- Fix debug when empty Question title.
- Add Assessment with H5P passed, H5P and Quizzes passed.

= 3.0.0 =
- Compatible with Learnpress 3.0.0.

