<!DOCTYPE html>
<style>
#fof{display:block; width:100%; padding:150px 0; line-height:1.6em; text-align:center;}
#fof .hgroup{margin-bottom:15px;}
#fof .hgroup h1, #fof .hgroup h2{margin:25; padding:0;}
#fof .hgroup h1{margin-bottom:15px; font-size:40px;}
#fof .hgroup h2{display:inline-block; padding:0 0 10px 0; font-size:80px; border:solid #CCCCCC; border-width:1px 0;}
#fof p{display:block; margin:15px 0 0 0; padding:0; font-size:16px;}
#fof p:first-child{margin-top:0;}
</style>
<div class="wrapper row2">
  <div id="container" class="clear">
    <section id="fof" class="clear">
      <div class="hgroup">
        <h1><?php echo $error_message ?></h1>
        <h2>Error <?php echo $error_code ?></h2>
      </div>
      <p><?php echo $error_explanation ?></p>
      <p>Go to <a href="./login/auth">Login</a> page or go <a href="javascript:history.back()">Back</a></p>
    </section>
  </div>
</div>
