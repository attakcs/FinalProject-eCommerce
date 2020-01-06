<h3 class="divider">Product info</h3>

<section class="product-info">

<div class="row">
                <!-- Picture -->
                <div class="col-md">
                    <img src="" id="pro-image" class="img-fluid mx-auto d-block">
                </div>

                <!-- Info -->
                <div class="col-md">
                    <h4 class="card-title" id="pro-product"></h4>       
        
                    <p class="text-justify">Category: <span class="product-tag" id="pro-category"></span></p>
                    <p class="text-justify">Price: <span class="product-tag" id="pro-price"></p>
                    <p class="text-justify">Status: <span class="product-tag" id="pro-status"></span></p>
                    <p class="text-justify">Quantity:  <span class="product-tag" id="pro-quantity"></span></p>
                    <p class="text-justify">Brief:  <span class="product-tag" id="pro-brief"></span></p>
                </div>
</div>

<div class="row">

    <div class="col-md m-3"><h4>Description: </h4><p class="text-justify" id="pro-description"></p></div>
    
</div>
    
</section>

<section class="product-reviews">
    <h2>Reviews</h2>

    <div id="product-reviews"></div>
</section>

<section class="product-questions">
    <h2>Questions</h2>

    <div id="product-questions"></div>

    <div class="ask-questions">
        <h2>Ask a question</h2>

        <?php if(GetUserGroup() == 'Guest'):?>
            <p>Please login to ask questions</p>
        <?php else:?>
            <form id="frmQuestion">
                <p>
                    <label for="question">Question</label>
                    <input type="text" id="question" name="question">
                </p>
                <p>
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </p>
                
                <p class="form-operations">
                    <button type="submit" class="btn btn-pill btn-info">Ask Question</button>
                </p>
            </form>
        <?php endif?>
    </div>
</section>

<button type="button" class="btn btn-pill btn-dark" onclick="location.href='/Product-Catalog'">&#x2B9C; Back</button>

<button type="button" class="btn btn-pill btn-success">Buy</button>


<?php if(GetUserGroup() == 'Administrator'):?>
    <form id="frmAnswer">
        <input type="hidden" id="question_id" name="question_id" value="0">
        <p>
            <label for="answer">Answer</label>
            <textarea id="answer" name="answer"></textarea>
        </p>
        <p class="form-operations">
            <button type="submit" class="btn btn-pill btn-info button">Send</button>
        </p>
    </form>
<?php endif ?>

<script>
const productID = `<?= GetURISegments()[1]??0 ?>`;
const frmReview = $('#frmReview');
const frmQuestion = $('#frmQuestion');

<?php if(GetUserGroup() == 'Administrator'):?>
    const frmAnswer = $('#frmAnswer');
    frmAnswer.remove();

    function WriteAnswer(questionID){
        const questionAnswers = $('#answer_'+questionID);

        questionAnswers.prepend(frmAnswer);
        $('#question_id').value = questionID;
    }

    frmAnswer.addEventListener('submit', function(e){
        e.preventDefault();
        const questionID = $('#question_id').value;
        
        Ajax('POST', '/api/Answer/Create',
            {
                question_id: questionID,
                answer: $('#answer').value
            },
            function(resp) {
                if (ErrorInResponse(resp)) {
                    return false;
                }

                frmAnswer.reset();
                frmAnswer.remove();

                const a =resp.data[0];
                let container = document.createElement('div');
                container.className='answer';

                container.innerHTML = `<p>${a.date_added}</p>
                    <div>${a.answer}</div>
                    <p>
                        <img class="user-photo" src="/api/User/Photo/${a.photo}">
                        <span>${a.user}</span>
                    </p>`;

                $('#answer_'+questionID).prepend(container);
            });
    });
<?php endif ?>

if(frmQuestion){
    frmQuestion.addEventListener('submit', function(e){
        e.preventDefault();

        Ajax('POST', '/api/Question/Create',
            {
                product_id: productID,
                question: $('#question').value,
                description: $('#description').value
            },
            function(resp) {
                if (ErrorInResponse(resp)) {
                    return false;
                }

                frmQuestion.reset();
            });
    });
}

function GetProductReviews(productID){
    Ajax('POST', '/api/Product/Reviews',
        {product_id: productID},
        function(resp) {
            if (ErrorInResponse(resp)) {
                return false;
            }

            const productReviews = $('#product-reviews');
            for(let r of resp.data){
                let container = document.createElement('div');
                container.className='review';

                r.stars *= 16
                container.innerHTML = `<p><span class="stars-bg"><span class="stars-count" style="width:${r.stars}px;">&nbsp;</span></span> <span>${r.date_added}</span></p>
                    <div>${r.review}</div>
                    <p>
                        <img class="user-photo" src="/api/User/Photo/${r.photo}">
                        <span>${r.customer}</span>
                    </p>`;

                productReviews.appendChild(container);
            }
        });
}

function GetProductQuestions(productID){
    Ajax('POST', '/api/Product/Questions',
        {product_id: productID},
        function(resp) {
            if (ErrorInResponse(resp)) {
                return false;
            }

            const productQuestions = $('#product-questions');
            for(let q of resp.data){
                let container = document.createElement('div');
                container.className='question';

                let html = `<h5>${q.question}</h5>
                    <div>${q.description}</div>
                    <p>
                        <img class="user-photo" src="/api/User/Photo/${q.photo}">
                        <span>${q.customer}</span>
                        <span>${q.date_added}</span>
                    </p>
                    <button type="button" onclick="ViewAnswers(${q.question_id})" class="btn btn-info btn-sm">View Answers (${q.answers})</button>
                    `;
                
                <?php if(GetUserGroup() == 'Administrator'):?>
                    html += `<button type="button" onclick="WriteAnswer(${q.question_id})" class="btn btn-info btn-sm">Write Answer</button>`
                <?php endif ?>

                html += `</p>
                    <div class="answers-container" id="answer_${q.question_id}"></div>`;

                container.innerHTML = html;
                productQuestions.appendChild(container);
            }
        });
}

function ViewAnswers(questionID){
    const questionAnswers = $('#answer_'+questionID);
    questionAnswers.innerHTML = '';

    Ajax('POST', '/api/Question/ReadAnswers',
        {question_id: questionID},
        function(resp) {
            if (ErrorInResponse(resp)) {
                return false;
            }

            for(let a of resp.data){
                let container = document.createElement('div');
                container.className='answer';

                container.innerHTML = `<p>${a.date_added}</p>
                    <div>${a.answer}</div>
                    <p>
                        <img class="user-photo" src="/api/User/Photo/${a.photo}">
                        <span>${a.user}</span>
                    </p>`;

                questionAnswers.appendChild(container);
            }
        });
}

Ajax('POST', '/api/Product/Read/'+productID,
    null,
    function(resp) {
        if (ErrorInResponse(resp)) {
            return false;
        }

        const productInfo = $('#product-info');
        let p = resp.data[0];
        $('#pro-product').innerHTML = `${p.product} Specs`;
        $('#pro-category').innerHTML = p.category;
        $('#pro-price').innerHTML = `<?= CURRENCY ?>${p.price}`;
        $('#pro-quantity').innerHTML = p.quantity;
        $('#pro-status').innerHTML = p.status;
        $('#pro-image').src =`/api/Product/Image/${p.image}`;
        $('#pro-brief').innerHTML = p.brief;
        $('#pro-description').innerHTML = NL2P(p.description);

        GetProductReviews(productID);
        GetProductQuestions(productID);
    });
</script>
