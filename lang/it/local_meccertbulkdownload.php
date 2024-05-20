<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'local_meccertbulkdownload', language 'it'.
 *
 * @package    local_meccertbulkdownload
 * @author     MoodEasy
 * @copyright  (c) 2024 onwards MoodEasy (moodeasy.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['all'] = '(tutti)';
$string['archivenametemplatesitem'] = 'Template per i nomi dei file compressi';
$string['archivenametemplatesitem_desc'] = '<p>I template per il nome dei file compressi funzionano esattamente come quelli per i pdf. I parametri utilizzabili sono però in numero inferiore:</p>
<table style="margin-bottom: 18px;">
<tr><td><strong>{{courseshortname}}</strong></td><td style="padding-left: 25px;">Nome breve corso</td></tr>
<tr><td><strong>{{coursecode}}</strong></td><td style="padding-left: 25px;">Codice corso</td></tr>
<tr><td><strong>{{cohortname}}</strong></td><td style="padding-left: 25px;">Nome gruppo globale</td></tr>
<tr><td><strong>{{groupname}}</strong></td><td style="padding-left: 25px;">Nome gruppo (solo se esportazione di singolo gruppo interno al corso)</td></tr>
<tr><td><strong>{{todaysdate(...)}}</strong></td><td style="padding-left: 25px;">Data di oggi</td></tr>
</table>
<p style="margin-bottom: 30px; color: red;"><strong>Attenzione: se due file hanno lo stesso nome il nuovo sovrascrive il vecchio.</strong></p>';
$string['archivenametemplatesitemsingular'] = 'Template per il nome del file compresso';
$string['bookconfirmmsg'] = 'Il file compresso con i certificati che sarà generato avrà una dimensione stimata di';
$string['bookconfirmmsgfreespace'] = 'Lo spazio libero nel server è (il valore rilevato potrebbe non essere corretto):';
$string['bookconfirmmsglightversion'] = "La versione light del plugin permette di scaricare massimo {HOW MANY_CERT} certificati.";
$string['bookconfirmmsgnb'] = 'N.B. la stima relativa al file compresso è basata sulla dimensione media dei certificati indicata nelle configurazioni del plugin.';
$string['bookconfirmmsgnotenoughspace'] = 'ATTENZIONE: lo spazio sul server sembra non sufficiente per generare il file.';
$string['bookconfirmmsgserver'] = 'Lo spazio necessario nel server per generarlo è tuttavia doppio rispetto alla dimensione del file, quindi';
$string['bulkdownloadlink'] = 'Scarica i certificati';
$string['certcreation'] = 'Data emissione certificato';
$string['certificateissuing'] = 'Periodo emissione certificato';
$string['cohort'] = 'Gruppo globale';
$string['courseandgroup'] = 'Corso e gruppo (del corso)';
$string['coursecompletion'] = 'Periodo completamento corso';
$string['coursecompletiondate'] = 'Data completamento corso';
$string['coursecompletionfrom'] = 'Da';
$string['coursecompletionto'] = 'A';
$string['createmanagestring'] = 'Creazione e gestione pacchetti';
$string['createmanagestring_desc'] = 'Permette di andare alla pagina dove creare, scaricare e gestire i pacchetti di certificati.<br>&nbsp;';
$string['credit'] = 'MoodEasy.com';
$string['deleteconfirmmsg'] = 'Procedo con l\'eliminazione del seguente file?';
$string['deleteerror'] = 'Errore: file non cancellato';
$string['deletenoparam'] = 'Errore: parametro mancante';
$string['deletesuccess'] = 'File eliminato con successo';
$string['errornotemplate'] = 'Nelle impostazioni del plugin devono essere definiti almeno un template per il nome dei file pdf e almeno uno per il nome dei file compressi.';
$string['errornotemplateparameter'] = 'Errore: parametro mancante';
$string['errornotemplatereplacepack'] = 'Si è verificato un errore nella sostituzione dei parametri nel template per il nome dei file compressi.';
$string['errornotemplatereplacepdf'] = 'Si è verificato un errore nella sostituzione dei parametri nel template per il nome dei file pdf.';
$string['estimatedarchivesize'] = 'Dimensione media dei certificati (KB)';
$string['estimatedarchivesize_desc'] = '<p style="margin-bottom: 30px;">Dimensione media di un certificato considerando la media di tutti i certificati generati nei vari corsi, espressa in KB. Utilizzata per stimare la dimensione del file compresso da generare per il download dei certificati.</p>';
$string['formtemplatesubmit'] = 'Prenota download certificati visualizzati';
$string['introseltemplate'] = 'Seleziona un template per i nomi da dare ai file pdf e uno per il nome del file compresso finale con tutti i pdf.';
$string['meccertbulkdownload:createarchives'] = 'Creare pacchetti di certificati';
$string['meccertbulkdownload:deletearchives'] = 'Eliminare pacchetti di certificati';
$string['meccertbulkdownload:notifyarchivecreated'] = 'Ricevere notifica quando un pacchetto di certificati è stato creato';
$string['meccertbulkdownload:searchcertificates'] = 'Cercare i certificati';
$string['meccertbulkdownload:viewarchives'] = 'Vedere la lista dei pacchetti di certificati e scaricarli';
$string['messageprovider:confirmation'] = 'Conferma fine preparazione pacchetto certificati';
$string['msgconfirmationcontexturlname'] = 'lista dei pacchetti';
$string['msgconfirmationfullmessage'] = 'Il seguente pacchetto zip di certificati è pronto: ';
$string['msgconfirmationfullmessagehtml'] = 'Il seguente pacchetto zip di certificati è pronto: ';
$string['msgconfirmationsmallmessage'] = 'Il seguente pacchetto zip di certificati è pronto: ';
$string['msgconfirmationsubject'] = 'Pachetto certificati pronto';
$string['nocertificatesfound'] = 'Nessun certificato trovato';
$string['packscreate'] = 'Crea pacchetti certificati';
$string['packsdownload'] = 'Gestisci e scarica pacchetti certificati';
$string['pdfnametemplatesitem'] = 'Template per i nomi dei file pdf';
$string['pdfnametemplatesitem_desc'] = '<p>Ogni riga rappresenta un template. Ad es. "Semplice:file_certificato", la prima parte fino ai due punti ("Semplice") è il nome del template, la seconda ("file_certificato") è il nome da dare al file (<strong>l\'estensione ".pdf" viene aggiunta automaticamente</strong>). Il <strong>nome del template (prima parte) deve essere una singola parola (senza spazi) e il template in generale non deve contenere caratteri particolari (solo lettere, numeri, underscore e segno meno).</strong></p><p><strong>Nel template (dopo i due punti) è possibile inserire alcuni parametri (racchiusi fra parentesi graffe) che saranno poi sostituiti dai valori corrispondenti</strong> (es. {{username}} sarà sostituito dallo username dell’utente). Il parametro <b>{{todaysdate(…)}}</b> ha un comportamento particolare, sarà sostituito dalla data odierna nel formato indicato dalle lettere indicate fra presenti. Ad esempio, se oggi è il 25/12/2023, {{todaysdate(d-m-Y)}} diventerà 25-12-2023. Stesso comportamento per il parametro <b>{{courseenddate(...)}}</b> che però restituirà la data di completamento del corso nel formato indicato. <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">Qui trovi i caratteri utilizzabili per formattare le date.</a> <span style="color: red;">Attenzione a non mettere slash e backslash (o altri caratteri particolari) come separatori nelle date.</span></p><p>I parametri utilizzabili sono:</p>
<table style="margin-bottom: 18px;">
<tr><td><strong>{{username}}</strong></td><td style="padding-left: 25px;">Username</td></tr>
<tr><td><strong>{{userfullname}}</strong></td><td style="padding-left: 25px;">Nome e cognome</td></tr>
<tr><td><strong>{{usersurname}}</strong></td><td style="padding-left: 25px;">Cognome</td></tr>
<tr><td><strong>{{courseshortname}}</strong></td><td style="padding-left: 25px;">Nome breve corso</td></tr>
<tr><td><strong>{{coursecode}}</strong></td><td style="padding-left: 25px;">Codice corso</td></tr>
<tr><td><strong>{{cohortname}}</strong></td><td style="padding-left: 25px;">Nome gruppo globale</td></tr>
<tr><td><strong>{{todaysdate(...)}}</strong></td><td style="padding-left: 25px;">Data di oggi</td></tr>
<tr><td><strong>{{courseenddate(...)}}</strong></td><td style="padding-left: 25px;">Data di fine corso</td></tr>
</table>
<p style="margin-bottom: 30px; color: red;"><strong>Attenzione: se due file hanno lo stesso nome il nuovo sovrascrive il vecchio.</strong></p>';
$string['pluginname'] = 'ME CustomCert Bulk Download';
$string['pluginname_help'] = 'Consente di selezionare un gruppo di certificati di Custom Cert selezionandoli per corso, gruppo o data, e di scaricarli in blocco e in background.';
$string['preview'] = 'Anteprima';
$string['queuetasksuccess'] = 'Il task è stato accodato e sarà eseguito appena possibile. Il file risultante comparirà qui sotto.';
$string['searchfor'] = 'Cerca per';
$string['tablerecordscount'] = 'Record da {{from}} a {{to}} di {{count}} - Per pagina: ';
