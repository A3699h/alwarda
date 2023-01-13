import '../../css/front/blogList.scss';

const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

let currentPage = 1;
$(function () {
    Routing.setRoutingData(routes);

    $('.moreBtn').on('click', function (e) {
        e.preventDefault();
        let imagesPath = $(this).data('imagespath');
        $('#loading-spinner').show();
        $.get(Routing.generate('api_get_blogs', {
            page: ++currentPage
        })).then((res) => {
            if (res) {
                if (res.pages == currentPage) {
                    $('.moreBtn').remove();
                }
                let articles = res.articles;
                let articlesHTML = [];
                articles.forEach((article) => {
                    articlesHTML.push(`<div class="col-lg-4 col-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <img class="w-100" src="${imagesPath + '/' + article.image}" alt="blog image">
                                                </div>
                                                <div class="card-body">
                                                    <a target="_blank" href="${Routing.generate('front_blog_single', {slug: article.slug})}"><h5>${article.title}</h5></a>
                                                    <p>${article.descriptio}}.</p>
                                                </div>
                                            </div>
                                        </div>`);
                });
                $('.blogSection').append(articlesHTML);
            }
        }).catch((err) => {
            console.log(err)
        }).always(() => {
            $('#loading-spinner').hide();
        })
    });

    $('#loading-spinner').hide();
})
