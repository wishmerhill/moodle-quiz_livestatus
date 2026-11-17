<?php
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
 * Italian strings for the quiz live status report.
 *
 * @package   quiz_livestatus
 * @copyright 2026 Alberto Azzalini <alberto.azzalini@gmail.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['autosavedisabled'] = 'Il salvataggio automatico del quiz è disattivato: le risposte compaiono qui solo quando lo studente cambia pagina o consegna.';
$string['autosavenote'] = 'Le risposte compaiono qui quando il browser dello studente le ha salvate: al cambio pagina o con il salvataggio automatico (ogni {$a}). Lo studente non vede se le risposte sono giuste: questo dipende solo dal comportamento del quiz (es. feedback differito).';
$string['bar_summary'] = 'Giuste: {$a->correct}, parziali: {$a->partial}, sbagliate: {$a->wrong}, non date: {$a->unanswered}';
$string['barstyle'] = 'Colori della barra di avanzamento';
$string['barstyle_binary'] = 'Colore unico (verde o rosso)';
$string['barstyle_desc'] = 'Come viene colorata la parte completata della barra. A segmenti: verde per le giuste, giallo per le parziali, rosso per le sbagliate. Colore unico: verde se più della metà delle risposte date finora è giusta (una parziale vale mezza), altrimenti rosso.';
$string['barstyle_segments'] = 'A segmenti (giuste / parziali / sbagliate)';
$string['col_answered'] = 'Risposte date';
$string['col_attempt'] = 'Tentativo';
$string['col_correct'] = 'Giuste';
$string['col_lastactivity'] = 'Ultimo salvataggio';
$string['col_partial'] = 'Parziali';
$string['col_percentage'] = 'Percentuale';
$string['col_state'] = 'Stato';
$string['col_student'] = 'Studente';
$string['col_unanswered'] = 'Non date';
$string['col_wrong'] = 'Sbagliate';
$string['detail_correct'] = 'giusta';
$string['detail_error'] = 'non valutabile';
$string['detail_partial'] = 'parzialmente giusta';
$string['detail_question'] = 'Domanda {$a->number}: {$a->result}';
$string['detail_unanswered'] = 'non data';
$string['detail_wrong'] = 'sbagliata';
$string['lastupdate'] = 'Aggiornato alle {$a->time}, aggiornamento automatico ogni {$a->seconds} secondi.';
$string['livestatus'] = 'Stato in diretta';
$string['livestatusreport'] = 'Stato in diretta';
$string['noattempts'] = 'Nessun tentativo in corso o completato.';
$string['pluginname'] = 'Stato in diretta';
$string['privacy:metadata'] = 'Il report "Stato in diretta" mostra solo i dati dei tentativi esistenti e non memorizza dati personali.';

$string['refreshnow'] = 'Aggiorna ora';
$string['showdetails'] = 'Mostra le risposte per domanda';
$string['showdetails_desc'] = 'Il docente può cliccare su una riga per vedere a quali domande lo studente ha risposto correttamente e a quali no (solo giusto/sbagliato, non le risposte).';
$string['state_finished'] = 'Completato';
$string['state_inprogress'] = 'In corso';
$string['toggle_details'] = 'Clicca per mostrare o nascondere il risultato di ogni domanda';
