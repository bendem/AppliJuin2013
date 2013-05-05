<?php

function index($fla = null) {
	mysql_auto_connect();

	// On récupère les premières lettres de chaque unités
	$sql = sql_select('DISTINCT LEFT(nom, 1)', 'unite_fabrication');
	$r = mysql_query($sql);
	$data = mysql_fetch_all($r, MYSQL_NUM);

	$enabled = array();
	foreach ($data as $v) {
		$enabled[] = strtoupper($v[0]);
	}

	// On établit une condition pour sélectionner les unités nécessaires
	$cond = false;
	if($fla) {
		$cond = 'nom like "' . mysql_real_escape_string($fla[0]) . '%"';
	}

	$r = mysql_query(sql_select(
		'*',
		'unite_fabrication',
		$cond
	));
	$data = mysql_fetch_all($r);


	$alph = array();
	for ($i=0; $i < 26; $i++) {
		$alph[] = chr($i + ord('A'));
	}

	return array(
		'alph' => $alph,
		'active' => ($fla) ? strtoupper($fla[0]) : null,
		'enabled' => $enabled,
		'data' => $data,
		'del_confirm' => htmlentities('Êtes-vous sûr ? <a href="' . url(array(
			'action' => 'unit',
			'view' => 'del',
			'params' => array('%s')
		)) . '" class="del btn btn-warning">Oui</a>')
	);
}

function add() {
	kick();
	/* Définition du formulaire */
	$post = array(
		'nom' => array(
			'label' => 'Nom'
		),
		'capaciteMax' => array(
			'label' => 'Capacité Maximale'
		),
		'adresse' => array(
			'label' => 'Adresse'
		),
		'ville' => array(
			'label' => 'Ville'
		),
		'cp' => array(
			'label' => 'Code Postal'
		)
	);

	$errors = array();

	if(!empty($_POST)) {
		if(array_keys($_POST) == array_keys($post)) {
			/*
				Gestion des erreurs
			 */
			$errors = validate_unit($_POST);

			if(empty($errors)) {
				mysql_auto_connect();
				$tmp = $_POST;
				unset($tmp['capaciteMax']);
				unset($tmp['nom']);
				$r = mysql_query(sql_select('*', 'unite_fabrication', $tmp));
				if(mysql_fetch_all($r)) {
					session_set_flash('Il y a déjà une unité de fabrication à cette adresse...', 'warning');
				} else {
					$sql = sql_insert($_POST, 'unite_fabrication');
					if(mysql_query($sql)) {
						session_set_flash('Unité de fabrication ajoutée...');
					} else {
						session_set_flash("Erreur interne lors de l'ajout !", 'error');
					}

				}
			} else {
				session_set_flash('Il y a des erreurs dans le formulaire...', 'warning');
				inject_errors($post, $errors);
			}
		} else {
			session_set_flash('Formulaire incorect...', 'error');
		}
	}

	return array(
		'post' => $post
	);
}

function del($params) {
	/**
	 * Penser à supprimer les produits correspondants !!!
	 */
	kick();
	if(is_numeric($params[0])) {
		$id = (int) $params[0];

		mysql_auto_connect();
		$q = 'DELETE FROM unite_fabrication WHERE num=' . $id;
		if(mysql_query($q)) {
			session_set_flash('Unité supprimée avec succès', 'success');
		} else {
			session_set_flash('Erreur interne', 'error');
		}
	} else {
		session_set_flash('Problème de paramètres pour la suppression', 'error');
	}
	redirect(url(array(
		'action' => 'unit'
	)));
}

function edit($params) {
	kick();

	$champs = array(
		'num', 'nom',
		'capaciteMax',
		'adresse', 'ville',
		'cp'
	);

	mysql_auto_connect();

	/* Traitement du post */
	$errors = array();
	if(!empty($_POST)) {
		if(array_keys($_POST) == $champs) {

			$errors = validate_unit($_POST);

			if(empty($errors)) {
				$sql = sql_update($_POST, 'unite_fabrication', array('num' => $_POST['num']));
				$r = mysql_query($sql);
				if($r) {
					session_set_flash('Unité bien modifiée...');
				} else {
					session_set_flash('Erreur interne !', 'error');
				}
			}

		} else {
			session_set_flash('Mauvais formulaire', 'error');
		}
	}

	/* Récupération des infos de l'unité */
	$sql = sql_select('*', 'unite_fabrication', array(
		'num' => $params[0]
	));
	$r = mysql_query($sql);
	if(!$data = mysql_fetch_assoc($r)) {
		session_set_flash("Cette unité n'existe pas...", 'error');
		redirect(url(array(
			'action' => 'unit'
		)));
	}

	$post = array();
	foreach ($data as $k => $v) {
		$post[$k]['value'] = $v;
		$post[$k]['label'] = ucfirst($k);
		if($k == 'num') {
			$post[$k]['readonly'] = 'true';
			$post[$k]['class'] = 'disabled';
		} elseif($k == 'capaciteMax') {
			$post[$k]['label'] = 'Capacité Maximale';
		}
		if(isset($errors[$k])) {
			$post[$k]['help'] = $errors[$k];
			$post[$k]['state'] = 'warning';
		}
	}

	return array(
		'data' => $post
	);
}

function info($params) {
	if(is_numeric($params[0])) {
		$id = $params[0];
		mysql_auto_connect();
		$data = mysql_fetch_assoc(mysql_query(sql_select('*', 'unite_fabrication', array('num' => $id))));
	}
	return array(
		'd' => $data
	);
}
