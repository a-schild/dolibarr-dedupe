<?php
/* Deduplicate
 * Copyright (C) 2016 Aarboard AG, Andre Schild, www.aarboard.ch
 */

/**
 *	\file		swisspayments.php
 *	\ingroup	swisspayments
 *	\brief		Form to enter new PVR bills
 */
$res = 0;
if (! $res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (! $res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (! $res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
// The following should only be used in development environments
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) {
	$res = @include "../../../dolibarr/htdocs/main.inc.php";
}
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) {
	$res = @include "../../../../dolibarr/htdocs/main.inc.php";
}
if (! $res && file_exists("../../../../../dolibarr/htdocs/main.inc.php")) {
	$res = @include"../../../../../dolibarr/htdocs/main.inc.php";
}
if (! $res) {
	die("Main include failed");
}

global $db, $langs, $user;

require_once DOL_DOCUMENT_ROOT .'/fourn/class/fournisseur.facture.class.php';
require_once(DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");

//dol_include_once('/swisspayments/class/swisspayments.class.php');
//dol_include_once('/swisspayments/class/swisspaymentssoc.class.php');

// Load translation files required by the page
$langs->load("aardedupe@aardedupe");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'showcodefield');
$myparam = GETPOST('myparam', 'alpha');

// Access control
if ($user->societe_id > 0) {
	// External user
	accessforbidden();
}

if (! $user->rights->adherent->creer) accessforbidden();

//// Default action
//if (empty($action) && empty($id) && empty($ref)) {
//	$action='showcodefield';
//}

// Load object if id or ref is provided as parameter
//$object = new SwisspaymentsClass($db);
//if (($id > 0 || ! empty($ref)) && $action != 'add') {
//	$result = $object->fetch($id, $ref);
//	if ($result < 0) {
//		dol_print_error($db);
//	}
//}

/*
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 */



/*
 * VIEW
 *
 * Put here all code to build page
 */

llxHeader('', $langs->trans('Deduplicate members'), '');

echo "<h1>Potential duplicates</h1>";

    $sql = "SELECT a.rowid arowid,a.firstname afirstname, a.lastname alastname, a.email aemail, a.societe asociete,";
    $sql .= " a.address aaddress, a.zip azip, a.town atown, ";
    $sql .= " b.rowid browid, b.firstname bfirstname, b.lastname blastname, b.email bemail, b.societe bsociete, ";
    $sql .= " b.address baddress, b.zip bzip, b.town btown, ";
    $sql .= " concat(concat(soundex(a.firstname),soundex(a.lastname)),soundex(b.lastname)) soundval ";
    $sql .= "FROM ".MAIN_DB_PREFIX."adherent a join ".MAIN_DB_PREFIX."adherent b ";
    $sql .= "where soundex(a.firstname) = soundex(b.firstname) and soundex(a.lastname) = soundex(b.lastname) and a.rowid > b.rowid ";
    $sql .= " and a.statut <> 0 and b.statut<>0 ";
    $sql .= "order by soundval, arowid";

    $resql = $db->query($sql);
    if ($resql) {
            if ($db->num_rows($resql)) 
            {
                echo "<table>";
                $ispair= false;
                $soundval= "";
                $rowids= array();
                while($obj = $db->fetch_object($resql))
                {
                    if ($soundval != $obj->soundval)
                    {
                        $soundval= $obj->soundval;
                        $ispair= !$ispair;
                    }
                    if (!in_array($obj->arowid, $rowids))
                    {
                        array_push($rowids, $obj->arowid);
                        echo "\n<tr class='".($ispair ? "pair" : "impair") ." arow'>";
                        echo "<td><a href='../../adherents/card.php?rowid=".$obj->arowid."' target='_blank'>".$obj->arowid."</a></td><td>".
                                $obj->afirstname."</td><td>".$obj->alastname."</td><td>".$obj->asociete."</td><td>".$obj->aemail."</td>".
                                "<td>".$obj->aaddress."</td><td>".$obj->azip."</td><td>".$obj->atown."</td>";
                        echo "</tr>";
                    }
                    if (!in_array($obj->browid, $rowids))
                    {
                        array_push($rowids, $obj->browid);
                        echo "\n<tr class='".($ispair ? "pair" : "impair") ." brow'>";
                        echo "<td><a href='../../adherents/card.php?rowid=".$obj->browid."' target='_blank'>".$obj->browid."</a></td><td>".
                                $obj->bfirstname."</td><td>".$obj->blastname."</td><td>".$obj->bsociete."</td><td>".$obj->bemail."</td>".
                                "<td>".$obj->baddress."</td><td>".$obj->bzip."</td><td>".$obj->btown."</td>";
                        echo "</tr>";
                    }
                }
                echo "</table>";
            }
            else
            {
                echo "No potential duplicates found";
            }
            $db->free($resql);

            return 1;
    } else {
        //$this->error = "Error " . $this->db->lasterror();
        dol_syslog(__METHOD__ . " " . $db->lasterror(), LOG_ERR);

        return -1;
    } 
// Example 2: Adding links to objects
// The class must extend CommonObject for this method to be available
// $somethingshown = $myobject->showLinkedObjectBlock();

// End of page
llxFooter();
