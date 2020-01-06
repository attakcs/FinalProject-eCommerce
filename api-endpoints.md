- Admin Only
    [POST]      /api/Answer/Create
    [POST]      /api/Answer/Update
    [POST]      /api/Answer/Delete
    [POST]      /api/Category/Create
    [POST]      /api/Category/Update
    [POST]      /api/Category/Delete
    [POST]      /api/Country/Create
    [POST]      /api/Country/Update
    [POST]      /api/Country/Delete
    [GET, POST] /api/Coupon/Read{/id}
    [POST]      /api/Coupon/Create
    [POST]      /api/Coupon/Update
    [POST]      /api/Coupon/Delete
    [GET, POST] /api/CreditCardType/Read{/id}
    [POST]      /api/CreditCardType/Create
    [POST]      /api/CreditCardType/Update
    [POST]      /api/CreditCardType/Delete
    [GET, POST] /api/Invoice/Read{/id}
    [POST]      /api/Invoice/Update
    [POST]      /api/Invoice/UpdateStatus
    [POST]      /api/Invoice/Delete
    [POST]      /api/Product/Create
    [POST]      /api/Product/Update
    [POST]      /api/Product/Delete
    [GET, POST] /api/Question/Read{/id}
    [POST]      /api/Question/Update
    [POST]      /api/Question/Delete
    [GET, POST] /api/Review/Read{/id}
    [POST]      /api/Review/Update
    [POST]      /api/Review/Delete
    [POST]      /api/State/Create
    [POST]      /api/State/Update
    [POST]      /api/State/Delete
    [GET, POST] /api/Statistics/AdminPanel
    [GET, POST] /api/Statistics/LoyalCustomers
    [GET, POST] /api/Statistics/MonthlyIncome
    [GET, POST] /api/Statistics/MonthlyInvoices
    [GET, POST] /api/User/Read{/id}
    [POST]      /api/User/Create
    [POST]      /api/User/Update
    [POST]      /api/User/Delete

- Any logged in user
    [GET, POST] /api/Coupon/Redeem{/coupon}
    [GET, POST] /api/CreditCard/Read{/id}
    [GET, POST] /api/CreditCard/ReadValid
    [POST]      /api/CreditCard/Create
    [POST]      /api/CreditCard/Save
    [POST]      /api/CreditCard/Update
    [POST]      /api/CreditCard/Delete
    [GET, POST] /api/CreditCardType/ReadAvailable
    [GET, POST] /api/Invoice/ReadMyInvoices{/id}
    [GET, POST] /api/Invoice/ViewOrder{/id}
    [POST]      /api/Invoice/Create
    [POST]      /api/Question/Create
    [POST]      /api/Review/Create
    [GET, POST] /api/State/Read{/id}
    [GET, POST] /api/User/Profile
    [POST]      /api/User/UpdateProfile
    [POST]      /api/User/DeleteProfile
    [GET, POST] /api/User/Logout

- Any visitor
    [GET, POST] /api/Answer/Read{/id}
    [GET, POST] /api/Category/Read{/id}
    [GET, POST] /api/Category/Read{/id}
    [GET, POST] /api/Product/Read{/id}
    [GET, POST] /api/Product/ReadAvailable{/id}
    [GET, POST] /api/Product/ReadMulti{/[id,id2,..]}
    [GET, POST] /api/Product/Reviews{/id}
    [GET, POST] /api/Product/Questions{/id}
    [GET]       /api/Product/Image/{id}
    [GET, POST] /api/Question/ReadApproved/{product_id}
    [GET, POST] /api/Question/ReadAnswers/{question_id}
    [GET, POST] /api/Review/ReadApproved/{product_id}
    [GET, POST] /api/Statistics/TopSales
    [GET, POST] /api/Statistics/TopRated
    [POST]      /api/User/Register
    [POST]      /api/User/Login
    [GET]       /api/User/Photo