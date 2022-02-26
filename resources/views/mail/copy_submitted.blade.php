<?php $item = $copy->item ?>

<img class="item-cover" src="<?php echo $item->cover->thumbnailUrl ?>" alt="<?php echo $item->title ?>">
<h2 class="item-title"><?php echo $item->title ?></h2>
<h3 class="item-authors"><?php echo $item->authors ?></h3>

Hallo <?php echo $copy->ownedBy->first_name; ?><br>
<p>Du hast vor Kurzem ein Exemplar von "<?php echo $item->title ?>"<?php if($copy->edition){
  $edition = $copy->edition;
  echo " (".$edition->number.". Auflage, ".$edition->year."";
  if($edition->name){
    echo ' "'.$edition->name.'"';
  }
  echo ")";
} ?> zum Verkauf eingereicht.</p>
<br>
<h2 class="center">Exemplar-Code</h2>
<div class="uid-large"><?php echo substr($copy->uid, 0,3) ?> <?php echo substr($copy->uid, 3,6) ?></div>
<p>Schreibe diesen Code auf einen Zettel und bringe diesen am Buch an.</p>
<img src="https://schoolhub.ch/images/pickup.svg" class="icon" alt="Wie weiter?">
<div class="icon-side-text"><h2>Wie weiter?</h2>
Bring das Exemplar in den nächsten Tagen beim Bookstore PickUp vorbei, wo wir den Zustand bestimmen und es anschliessend im Store verfügbar machen werden.
Wir werden es für dich für CHF <?php echo $copy->price ?>.- verkaufen. Abzüglich einer Provision von <?php echo $copy->commission*100; ?>% erhältst du CHF <?php echo $copy->payback ?> von uns nach dem Verkauf.
</div>
<br><br>
<h2>Stornierung</h2>
Möchtest du das Buch doch nicht verkaufen? Dann storniere bitte deine Einreichung.
<a class="button" href="https://schoolhub.ch/?cancelorder=<?php echo $copy->order_hash ?>">Verkauf stornieren</a>
