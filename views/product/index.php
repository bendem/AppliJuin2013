<?php $title_for_layout = 'Produits' ?>

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
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th>#</th>
					<th>Nom</th>
					<th>Unité de mesure</th>
					<th>Prix</th>
					<th>Type</th>
					<th>Matière dangereuse</th>
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
						<td><?= $v['uniteMesure'] ?></td>
						<td><?= $v['prix'] ?></td>
						<td><?= $v['type'] ?></td>
						<td>
							<?= ($v['categorie']) ? 'oui' : 'non' ?>
						</td>
						<td>
							<?= actions($req['action'], array($v['nom'], $v['num']), $del_confirm) ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>