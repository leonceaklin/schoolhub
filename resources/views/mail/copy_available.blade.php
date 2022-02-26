<?php $item = $copy->item ?>

<img class="item-cover" src="<?php echo $item->cover->thumbnailUrl ?>" alt="<?php echo $item->title ?>">
<h2 class="item-title"><?php echo $item->title ?></h2>
<h3 class="item-authors"><?php echo $item->authors ?></h3>

Hallo <?php echo $copy->ownedBy->first_name; ?><br>
<p>Du hast ein Exemplar von "<?php echo $item->title ?>"<?php if($copy->edition){
  $edition = $copy->edition;
  echo " (".$edition->number.". Auflage, ".$edition->year."";
  if($edition->name){
    echo ' "'.$edition->name.'"';
  }
  echo ")";
} ?> zum Verkauf abgegeben. Wir haben es nun überprüft und im Store verfügbar gemacht.</p>
<br>
<img src="https://schoolhub.ch/images/pickup.svg" class="icon" alt="Wie weiter?">
<div class="icon-side-text"><h2>Wie weiter?</h2>
Wir werden es für dich nun für CHF <?php echo $copy->price ?>.- verkaufen. Abzüglich einer Provision von <?php echo $copy->commission*100; ?>% erhältst du CHF <?php echo $copy->payback ?> von uns nach dem Verkauf.
</div>
