
WidgetClientBackService

https://docs.google.com/document/d/1jVGSW2xEHAsCntS0DY7e0eFZLqT5RpCIgMkydeHbvKc/edit

Сервис по получению и обработке заказа от клиента

#[Route("/widget/{term}", name: "widget_entrypoint", methods: ["GET"])]
* @Route("/widget/{id}/order", name="make_order", methods={"POST"})
* @Route("/widget/order/xls", name="parse_order_xls", methods={"POST"})
* @Route("/certificate/pdf/{id}", name="show_pdf_certificate")
  #[Route('/widget/{id}/preview', name: 'certificate_preview', methods: ['GET'])]
* @Route("/{widget_id}/view/{id}", name="show_certificate", methods={"GET"})

#[Route('/widget/{id}/products', name: "products", methods: ['GET'])]


**Probe for Kubernetes**  
liveness: `GET /health`  
ожидаемый результат:  
`
200
`
`
{
  "status": "ok"
}
`  

readness: `GET /read`  
ожидаемый результат:  
`
200  
`
`
{
"status": "ok"
}
`

