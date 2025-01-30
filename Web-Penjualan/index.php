<?php 
	include "header.php"; 
?>

    <section id="home-section" class="hero">
		<div class="home-slider owl-carousel">
	      <div class="slider-item" style="background-image: url(images/bg_1.jpg);">
	      	<div class="overlay"></div>
	        <div class="container">
	          <div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">

	            <div class="col-md-12 ftco-animate text-center">
	              <h1 class="mb-2">FLOWER BY KHANSA</h1>
	              <h2 class="subheading mb-4">Every Flower is a Soul Blossoming in Nature</h2>
	              <p><a href="#kesini" class="btn btn-primary">LEBIH LANJUT</a></p>
	            </div>

	          </div>
	        </div>
	      </div>
	    </div>
    </section>

    <section class="ftco-section">
			<div class="container">
				<div class="row no-gutters ftco-services">
          <div class="col-md-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services mb-md-0 mb-4">
              <div class="icon bg-color-1 active d-flex justify-content-center align-items-center mb-2">
            		<span class="flaticon-award"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">Harga Terjangkau</h3>
                <span>Harga Murah dan Terjangkau</span>
              </div>
            </div>      
          </div>
          <div class="col-md-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services mb-md-0 mb-4">
              <div class="icon bg-color-2 d-flex justify-content-center align-items-center mb-2">
            		<span class="flaticon-customer-service"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">Produk Terjamin</h3>
                <span>Produk Yang Dijual Terjamin</span>
              </div>
            </div>    
          </div>
          <div class="col-md-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services mb-md-0 mb-4">
              <div class="icon bg-color-3 d-flex justify-content-center align-items-center mb-2">
            		<span class="flaticon-award"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">Kualitas Terjamin</h3>
                <span>Kualitas dari produk telah terjamin</span>
              </div>
            </div>      
          </div>
          <div class="col-md-3 text-center d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services mb-md-0 mb-4">
              <div class="icon bg-color-4 d-flex justify-content-center align-items-center mb-2">
            		<span class="flaticon-customer-service"></span>
              </div>
              <div class="media-body">
                <h3 class="heading">Fast Respon</h3>
                <span>Fast Respon dan Ramah</span>
              </div>
            </div>      
          </div>
        </div>
			</div>
		</section>

		<section class="ftco-section ftco-category ftco-no-pt">
			<div class="container">
				<div class="row">
					<div class="col-md-8">
						<div class="row">
							<div class="col-md-6 order-md-last align-items-stretch d-flex">
								<div class="category-wrap-2 ftco-animate img align-self-stretch d-flex" style="background-image: url(images/category.jpg);">
									
								</div>
							</div>
							<div class="col-md-6">
								<div class="category-wrap ftco-animate img mb-4 d-flex align-items-end" style="background-image: url(images/category-1.jpg);">
								</div>
								<div class="category-wrap ftco-animate img d-flex align-items-end" style="background-image: url(images/category-2.jpg);">
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-4">
						<div class="category-wrap ftco-animate img mb-4 d-flex align-items-end" style="background-image: url(images/category-3.jpg);">	
						</div>
						<div class="category-wrap ftco-animate img d-flex align-items-end" style="background-image: url(images/category-4.jpg);">
						</div>
					</div>
				</div>
			</div>
		</section>

		<hr>

    <section class="ftco-section">
    	<div class="container">
				<div class="row justify-content-center mb-3 pb-3">
          <div class="col-md-12 heading-section text-center ftco-animate">
          	<span class="subheading">Produk yang Dijual</span>
            <h2 class="mb-4">Our Products</h2>
          </div>
        </div>   		
    	</div>
    	<div class="container">
    		<div class="row">

				<?php
				// Query untuk mengambil data produk
				$productSql = "SELECT * FROM produk LIMIT 4";
				$productResult = $conn->query($productSql);
				?>

				<?php foreach($productResult as $row) : ?>
    			<div class="col-md-6 col-lg-3 ftco-animate">
    				<div class="product">
						<a href="product-single.php?idproduk=<?=$row["idproduk"];?>" class="img-prod"><img class="img-fluid" src="../produk/<?php echo $row["gambar"]; ?>" alt="<?php echo $row["namaproduk"]; ?>">
							<div class="overlay"></div>
						</a>
    					<div class="text py-3 pb-4 px-3 text-center">
    						<h3><a href="#"><?php echo $row["namaproduk"]; ?></a></h3>
    						<div class="d-flex">
    							<div class="pricing">
		    						<p class="price"><span>Rp.<?php echo $row["hargaafter"]; ?></span></p>
		    					</div>
	    					</div>
							<div class="bottom-area d-flex px-3">
								<div class="m-auto d-flex">
									<p class="price"><span>Klik untuk info lebih lanjut</span></p>
								</div>
							</div>
    					</div>
    				</div>
    			</div>
				<?php endforeach; ?>
    		</div>
    	</div>
    </section>
		
	

    <section class="ftco-section testimony-section" id="kesini">
      <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section ftco-animate text-center">
          	<span class="subheading">Tentang Kami</span>
            <h2 class="mb-4">Flower by Khansa</h2>
            <p>
			Every Flower is a Soul Blossoming in Nature
			</p>
          </div>
        </div>
      </div>
    </section>

<?php include "footer.php"; ?>