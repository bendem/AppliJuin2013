<?php

function index() {
	mysql_auto_connect();

	$r = mysql_query(sql_select('*', 'produit'));
	$data = mysql_fetch_all($r);

	return array(
		'data' => $data,
		'del_confirm' => htmlentities('Êtes-vous sûr ? <a href="' . url(array(
			'action' => 'command',
			'view' => 'del',
			'params' => array('%s', '%s')
		)) . '" class="del btn btn-warning">Oui</a>')
	);
}

function add(array $params = null) {
	mysql_auto_connect();

	$numUnite = false;
	$numDepot = false;
	$numeric = array(0, 2, 3, 5);
	$size = array(0, 3, 6);
	if(!in_array(sizeof($params), $size)) {
		var_dump(sizeof($params));
		die("Erreur d'adresse (mauvais nombre de paramètres)");
	} else {
		foreach ($numeric as $v) {
			if(isset($params[$v]) && !is_numeric($params[$v])) {
				die("Erreur d'adresse (paramètres numériques demandé)");
			}
		}
	}

	if($params) {
		if($params[0] == 1) {
			$numUnite = $params[2];
			if(sizeof($params) == 6) {
				$numDepot = $params[5];
			}
		} else {
			$numDepot = $params[2];
			if(sizeof($params) == 6) {
				$numDepot = $params[5];
			}
		}
	}

	if($numUnite) {
		$r = mysql_query(sql_select('num, nom', 'unite_fabrication'));
		$d = var_dump(mysql_fetch_all($r));
	}

	$champs = array(
		'numUnite' => array(
			'label' => 'Unité de fabrication',
			'type' => 'select',
			'values' => array(2 => 'lol', 1 => 'test'),
			'value' => (int) $numUnite
		),
		'numDepot' => array(
			'label' => 'Dépôt',
			'type' => 'select',
			'values' => array(),
			'value' => (int) $numDepot
		)
	);

	return array(
		'champs' => $champs
	);
}
