<?php $item = $copy->item ?>

<img class="item-cover" src="<?php echo $item->cover->thumbnailUrl ?>" alt="<?php echo $item->title ?>">
<h2 class="item-title"><?php echo $item->title ?></h2>
<h3 class="item-authors"><?php echo $item->authors ?></h3>
<h2 class="item-title">CHF <?php echo $copy->price ?>.-</h2>


Hallo {{ $copy->orderedBy->first_name }}<br>
<p>Du hast vor Kurzem "<?php echo $item->title ?>" von <?php echo $item->authors ?><?php if($copy->edition){
  $edition = $copy->edition;
  echo " (".$edition->number.". Auflage, ".$edition->year."";
  if($edition->name){
    echo ' "'.$edition->name.'"';
  }
  echo ")";
} ?> im Bookstore bestellt. Vielen Dank dafür.</p>
<p>Referenz-Code: <span class="monospace"><?php echo $copy->uid ?>-<?php echo $item->isbn ?></span></p>
<img src="https://schoolhub.ch/images/pickup.svg" class="icon" alt="Abholung und Bezahlung">
<div class="icon-side-text"><h2>Abholung und Bezahlung</h2>
Abholen und bezahlen kannst du deine Bestellung beim Bookstore PickUp neben dem Lichthof. Bitte beachte, dass wir nur Zahlungen in Bar entgegennehmen können. Du brauchst keine Bestätigung der Bestellung. Sag uns einfach, wer du bist.
</div>
<br><br>
<h2>Stornierung</h2>
Hast du etwas falsches bestellt? Du kannst die Bestellung hier stornieren. Es fallen keine Gebühren an.
<a class="button" href="https://schoolhub.ch/?cancelorder=<?php echo $copy->order_hash ?>">Bestellung stornieren</a>
