@managing_orders
Feature: Seeing the currency in which all orders have been placed
    In order to know in which currency a customer will pay
    As an Administrator
    I want to be able to see orders' total in it's currency

    Background:
        Given the store ships to "British Virgin Islands"
        And the store has a zone "English" with code "EN"
        And this zone has the "British Virgin Islands" country member
        And the store operates on a channel named "Web"
        And that channel allows to shop using "USD" and "GBP" currencies
        And that channel uses the "USD" currency by default
        And the store allows paying with "Cash on Delivery"
        And the store has "DHL" shipping method with "$20.00" fee within the "EN" zone
        And the store has a product "Angel T-Shirt" priced at "$20.00"
        And there is a customer "Lucy" identified by an email "lucy@teamlucifer.com" and a password "pswd"
        And there is a customer "Satin" identified by an email "satin@teamlucifer.com" and a password "pswd"
        And I am logged in as an administrator

    @ui
    Scenario: Seeing an order placed in the base currency
        Given there is a customer "lucy@teamlucifer.com" that placed an order "#00000666"
        And the customer bought a single "Angel T-Shirt"
        And the customer "No Face" addressed it to "Lucifer Morningstar", "Seaside Fwy" "90802" in the "United States"
        And for the billing address of "Mazikeen Lilim" in the "Pacific Coast Hwy", "90806" "Los Angeles", "United States"
        And the customer chose "DHL" shipping method with "Cash on Delivery" payment
        When I browse orders
        Then I should see the order "#00000666" with total "$40.00"

    @ui
    Scenario: Seeing an order placed in a currency other than the base
        Given there is a customer "satin@teamlucifer.com" that placed an order "#00000666"
        And the customer has chosen to order in the "GBP" currency
        And the customer bought a single "Angel T-Shirt"
        And the customer "No Face" addressed it to "Lucifer Morningstar", "Seaside Fwy" "90802" in the "United States"
        And for the billing address of "Mazikeen Lilim" in the "Pacific Coast Hwy", "90806" "Los Angeles", "United States"
        And the customer chose "DHL" shipping method with "Cash on Delivery" payment
        When I browse orders
        Then I should see the order "#00000666" with total "£40.00"

    @ui
    Scenario: Seeing multiple orders in different currencies
        Given there is a customer "lucy@teamlucifer.com" that placed an order "#00000666"
        And the customer bought a single "Angel T-Shirt"
        And the customer "No Face" addressed it to "Lucifer Morningstar", "Seaside Fwy" "90802" in the "United States"
        And for the billing address of "Mazikeen Lilim" in the "Pacific Coast Hwy", "90806" "Los Angeles", "United States"
        And the customer chose "DHL" shipping method with "Cash on Delivery" payment
        And there is a customer "satin@teamlucifer.com" that placed an order "#00666000"
        And the customer has chosen to order in the "GBP" currency
        And the customer bought a single "Angel T-Shirt"
        And the customer "No Face" addressed it to "Lucifer Morningstar", "Seaside Fwy" "90802" in the "United States"
        And for the billing address of "Mazikeen Lilim" in the "Pacific Coast Hwy", "90806" "Los Angeles", "United States"
        And the customer chose "DHL" shipping method with "Cash on Delivery" payment
        When I browse orders
        Then I should see the order "#00000666" with total "$40.00"
        And I should see the order "#00666000" with total "£40.00"
