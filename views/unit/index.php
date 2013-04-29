<?php $title_for_layout = 'Unités de fabrication' ?>

<div class="row-fluid">
	<div class="span12">
		<div class="btn-group">
			<?php foreach ($alph as $v): ?>
				<a href="<?= url(array_merge($req, array('params' => array(strtolower($v))))) ?>" class="btn <?= (!in_array($v, $enabled)) ? 'disabled ' : '' ?><?= ($v == $active) ? 'active' : '' ?>"><?= $v ?></a>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<a href="<?= url(array('action' => $req['action'], 'view' => 'add')) ?>" class="btn btn-block btn-large btn-info">
			Ajouter une unité de fabrication
		</a>
	</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th>#</th>
					<th>Nom</th>
					<th>Adresse</th>
					<th>CP</th>
					<th>Ville</th>
					<th>Capacité maximale</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if(empty($data)): ?>
					<tr>
						<td colspan="7" style="text-align: center;">
							<div class="alert alert-error">Pas de données</div>
						</td>
					</tr>
				<?php endif; ?>
				<?php foreach($data as $v): ?>
					<tr>
						<td><?= $v['num'] ?></td>
						<td><?= $v['nom'] ?></td>
						<td><?= $v['adresse'] ?></td>
						<td><?= $v['ville'] ?></td>
						<td><?= $v['cp'] ?></td>
						<td><?= $v['capaciteMax'] ?></td>
						<td>
							<a href="<?= url(array(
								'action' => 'command',
								'view' => 'add',
								'params' => array(
									1, $v['nom'], $v['num'] // le 1 sert à préciser que c'est une unité de fabrication
								))) ?>" class="btn" data-toggle="tooltip" data-title="Effectuer une commande">
								<span class="icon-globe"></span>
							</a>
							<?= actions($req['action'], array($v['nom'], $v['num']), $del_confirm) ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
