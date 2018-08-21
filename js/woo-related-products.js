jQuery(document).ready(function() {
  
  /* The unwrap lines here get rid of elements not needed in the html
  when we are using slick slider - woo commerce has elements we dont want*/
  
  jQuery('.wrp-card').unwrap();
 jQuery('.wrp-card').unwrap();
   jQuery('.wrp-card').unwrap();
  jQuery('.wrp-carousel h2').remove();
  
  /* Initialise the carousel */
 jQuery('.wrp-carousel').slick({
   infinite: true,
  slidesToShow: 4,
  slidesToScroll: 1,
  autoplay: true,
  arrows: true,
   dots: false,
  autoplaySpeed: 2000,
    responsive: [
      {
        breakpoint: 992,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 2,
        }
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 576,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      }
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ]
    
});
  
  
});