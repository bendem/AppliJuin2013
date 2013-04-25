<?php

function index($fla = null) {
	mysql_auto_connect();

	// On récupère les premières lettres de chaque dépôt
	$sql = sql_select('DISTINCT LEFT(nom, 1)', 'depot');
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
		'depot',
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
			'action' => 'depot',
			'view' => 'del',
			'params' => array('%s', '%s')
		)) . '" class="del btn btn-warning">Oui</a>')
	);
}

function add() {
	/* Définition du formulaire */
	$post = array(
		'nom' => array(
			'label' => 'Nom'
		),
		'adresse' => array(
			'label' => 'Adresse'
		),
		'ville' => array(
			'label' => 'Ville'
		),
		'cp' => array(
			'label' => 'Code Postal'
		),
		'capaciteStockage' => array(
			'label' => 'Capacité de stockage'
		),
		'responsable' => array(
			'type' => 'select',
			'label' => 'Responsable',
			'values' => array(
				'XA', 'GT', 'LP', 'RS', 'TT'
			)
		),
		'matiereDangereuse' => array(
			'label' => 'Matière dangereuse',
			'type' => 'checkbox'
		)
	);

	$errors = array();

	if(!empty($_POST)) {
		if(array_keys($_POST) == array_keys($post)) {
			/*
				Gestion des erreurs
			 */
			if(strlen($_POST['nom']) > 255 || strlen($_POST['nom']) < 3) {
				$errors['nom'] = 'Le nom doit comporter 3 à 255 caractères';
			}
			if(strlen($_POST['adresse']) > 255 || strlen($_POST['adresse']) < 3) {
				$errors['adresse'] = 'L\'adresse doit comporter 3 à 255 caractères';
			}
			if(strlen($_POST['ville']) > 255 || strlen($_POST['ville']) < 2) {
				$errors['ville'] = 'La ville doit comporter 2 à 255 caractères';
			}
			if(!preg_match('/[1-9][0-9]{3}/', $_POST['cp'])) {
				$errors['cp'] = 'Le code postal doit être un nombre à 4 chiffres';
			}
			if(!is_numeric($_POST['capaciteStockage'])) {
				$errors['capaciteStockage'] = 'La capacité de stockage doit être un nombre';
			}
			if(!in_array($_POST['responsable'], array_keys($post['responsable']['values']))) {
				$errors['responsable'] = 'Le responsable doit faire partie de la liste';
			}
			if(!in_array($_POST['matiereDangereuse'], array('on', 'off'))) {
				$errors['matiereDangereuse'] = 'Vous ne pouvez pas choisir votre valeur';
			} else {
				$_POST['matiereDangereuse'] = ($_POST['matiereDangereuse'] == 'on');
			}

			if(empty($errors)) {
				mysql_auto_connect();
				$_POST['responsable'] = $post['responsable']['values'][$_POST['responsable']];
				$sql = sql_insert($_POST, 'depot');
				if(mysql_query($sql)) {
					session_set_flash('Dépôt ajouté...');
				} else {
					session_set_flash("Erreur interne lors de l'ajout !", 'error');
				}
			} else {
				session_set_flash('Il y a des erreurs dans le formulaire...', 'warning');
				foreach ($post as $k => $v) {
					if(isset($errors[$k])) {
						$post[$k]['help'] = $errors[$k];
						$post[$k]['state'] = 'warning';
					} else {
						$post[$k]['state'] = 'success';
					}
					$post[$k]['value'] = $_POST[$k];
				}
			}
		} else {
			session_set_flash('Formulaire incorect...', 'error');
		}
	}

	return array(
		'post' => $post,
		'errors' => $errors
	);
}

function del($params) {
	/**
	 * Penser à supprimer les produits correspondants !!!
	 */
	kick();
	if(is_string($params[0]) && is_numeric($params[1])) {
		$nom = $params[0];
		$id = (int) $params[1];
	} else {
		session_set_flash('Problème de paramètres pour la suppression', 'error');
		redirect(url(array(
			'action' => 'depot'
		)));
	}


	mysql_auto_connect();
	$q = 'DELETE FROM depot WHERE num=' . $id;
	if(mysql_query($q)) {
		session_set_flash('Dépôt avec succès', 'success');
	} else {
		session_set_flash('Erreur interne', 'error');
	}
	redirect(url(array(
		'action' => 'depot'
	)));
}

function edit($params) {
	kick();

	$champs = array(
		'num' => array(
			'label' => 'Numéro'
		),
		'nom' => array(
			'label' => 'Nom'
		),
		'adresse' => array(
			'label' => 'Adresse'
		),
		'ville' => array(
			'label' => 'Ville'
		),
		'cp' => array(
			'label' => 'Code Postal'
		),
		'capaciteStockage' => array(
			'label' => 'Capacité de stockage'
		),
		'responsable' => array(
			'type' => 'select',
			'label' => 'Responsable',
			'values' => array(
				'XA', 'GT', 'LP', 'RS', 'TT'
			)
		),
		'matiereDangereuse' => array(
			'label' => 'Matière dangereuse',
			'type' => 'checkbox'
		)
	);

	mysql_auto_connect();

	/* Traitement du post */
	$errors = array();
	if(!empty($_POST)) {
		if(array_keys($_POST) == array_keys($champs)) {
			/*
				Gestion des erreurs
			 */
			if(strlen($_POST['nom']) > 255 || strlen($_POST['nom']) < 3) {
				$errors['nom'] = 'Le nom doit comporter 3 à 255 caractères';
			}
			if(strlen($_POST['adresse']) > 255 || strlen($_POST['adresse']) < 3) {
				$errors['adresse'] = 'L\'adresse doit comporter 3 à 255 caractères';
			}
			if(strlen($_POST['ville']) > 255 || strlen($_POST['ville']) < 2) {
				$errors['ville'] = 'La ville doit comporter 2 à 255 caractères';
			}
			if(!preg_match('/[1-9][0-9]{3}/', $_POST['cp'])) {
				$errors['cp'] = 'Le code postal doit être un nombre à 4 chiffres';
			}
			if(!is_numeric($_POST['capaciteStockage'])) {
				$errors['capaciteStockage'] = 'La capacité de stockage doit être un nombre';
			}
			if(!in_array($_POST['responsable'], array_keys($champs['responsable']['values']))) {
				$errors['responsable'] = 'Le responsable doit faire partie de la liste';
			}
			if(!in_array($_POST['matiereDangereuse'], array('on', 'off'))) {
				$errors['matiereDangereuse'] = 'Vous ne pouvez pas choisir votre valeur';
			} else {
				$_POST['matiereDangereuse'] = ($_POST['matiereDangereuse'] == 'on');
			}

			if(empty($errors)) {
				$sql = sql_update($_POST, 'depot', array('num' => $_POST['num']));
				$r = mysql_query($sql);
				if($r) {
					session_set_flash('Dépôt bien modifié...');
				} else {
					session_set_flash('Erreur interne !', 'error');
				}
			}

		} else {
			session_set_flash('Mauvais formulaire', 'error');
		}
	}

	/* Récupération des infos de l'unité */
	$sql = sql_select('*', 'depot', array(
		'num' => $params[1]
	));
	$r = mysql_query($sql);
	if(!$data = mysql_fetch_assoc($r)) {
		session_set_flash("Ce dépôt n'existe pas...", 'error');
		redirect(url(array(
			'action' => 'depot'
		)));
	}

	foreach ($data as $k => $v) {
		$champs[$k]['value'] = $v;
		$champs[$k]['label'] = ucfirst($k);
		if($k == 'num') {
			$champs[$k]['readonly'] = 'true';
			$champs[$k]['class'] = 'disabled';
		} elseif ($k == 'responsable') {
			$champs[$k]['type'] = 'select';
		} elseif ($k == 'matiereDangereuse') {
			$champs[$k]['type'] = 'checkbox';
			if($v) {
				$champs[$k]['checked'] = true;
			}
		}
		if(isset($errors[$k])) {
			$champs[$k]['help'] = $errors[$k];
			$champs[$k]['state'] = 'warning';
		}
	}

	return array(
		'data' => $champs
	);
}
