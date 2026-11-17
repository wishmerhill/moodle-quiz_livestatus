// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Automatic refresh and expandable rows of the quiz live status report.
 *
 * Open rows and the scroll position are kept in sessionStorage, so they survive the refresh.
 *
 * @module     quiz_livestatus/refresh
 * @copyright  2026 Alberto Azzalini <alberto.azzalini@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const SELECTORS = {
    toggle: 'tr.qls-toggle',
    detail: (attemptid) => `tr.qls-detail[data-attemptid="${attemptid}"]`,
    caret: '.qls-caret',
};

/** @var {number} extra time, in ms, after which a saved scroll position is no longer restored. */
const SCROLL_GRACE = 30000;

/**
 * Read the saved state of the report page.
 *
 * @param {string} key the sessionStorage key.
 * @returns {Object} the state: open (attempt ids), scroll, time.
 */
const loadState = (key) => {
    const state = {open: [], scroll: 0, time: 0};
    try {
        return Object.assign(state, JSON.parse(window.sessionStorage.getItem(key)) || {});
    } catch (e) {
        // Storage not available: rows simply start closed.
        return state;
    }
};

/**
 * Show or hide the detail row of an attempt.
 *
 * @param {HTMLElement} row the attempt row.
 * @param {boolean} isopen whether the details must be shown.
 */
const setOpen = (row, isopen) => {
    const detail = document.querySelector(SELECTORS.detail(row.dataset.attemptid));
    if (!detail) {
        return;
    }
    detail.classList.toggle('d-none', !isopen);
    row.setAttribute('aria-expanded', isopen ? 'true' : 'false');
    const caret = row.querySelector(SELECTORS.caret);
    if (caret) {
        caret.textContent = isopen ? '▾' : '▸';
    }
};

/**
 * Set up the report page.
 *
 * @param {number} cmid the course module id, used to keep the state of each quiz apart.
 * @param {number} seconds seconds between automatic refreshes.
 */
export const init = (cmid, seconds) => {
    const key = 'quiz_livestatus_' + cmid;
    const delay = seconds * 1000;
    const state = loadState(key);
    let open = state.open;

    const save = () => {
        try {
            window.sessionStorage.setItem(key, JSON.stringify({open: open, scroll: window.scrollY, time: Date.now()}));
        } catch (e) {
            // Ignore.
        }
    };

    const toggle = (row) => {
        const id = row.dataset.attemptid;
        const isopen = !open.includes(id);
        open = open.filter((x) => x !== id);
        if (isopen) {
            open.push(id);
        }
        setOpen(row, isopen);
        save();
    };

    document.querySelectorAll(SELECTORS.toggle).forEach((row) => {
        if (open.includes(row.dataset.attemptid)) {
            setOpen(row, true);
        }
        row.addEventListener('click', (e) => {
            if (!e.target.closest('a, button, input')) {
                toggle(row);
            }
        });
        row.addEventListener('keydown', (e) => {
            if ((e.key === 'Enter' || e.key === ' ') && e.target === row) {
                e.preventDefault();
                toggle(row);
            }
        });
    });

    // Back to where the teacher was, but only right after a refresh.
    if (state.time && Date.now() - state.time < delay + SCROLL_GRACE) {
        window.scrollTo(0, state.scroll);
    }
    window.addEventListener('beforeunload', save);
    setTimeout(() => window.location.reload(), delay);
};
