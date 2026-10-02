<script setup>
  import { onMounted, ref, watch } from 'vue'

  const options = ref([])
  const product = ref(null)
  const productCount = ref(0)
  const selected = ref({})
  const isLoading = ref(false)
  const errorMessage = ref('')

  let initializing = true

  const getConfigurations = async (initializeSelections = false) => {
    isLoading.value = true
    errorMessage.value = ''

    try {
      const response = await fetch('/api/configurations.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          selected: selected.value
        })
      })

      if (!response.ok) {
        throw new Error('Unable to load product configurations.')
      }

      const data = await response.json()

      options.value = data.options
      productCount.value = data.product_count
      product.value = data.product

      if (initializeSelections) {
        data.options.forEach(option => {
          if (!Object.prototype.hasOwnProperty.call(selected.value, option.key)) {
            selected.value[option.key] = ''
          }
        })
      }
    } catch (error) {
      console.error(error)
      errorMessage.value = 'Unable to load product configurations. Please try again.'
    } finally {
      isLoading.value = false
    }
  }

  onMounted(async () => {
    await getConfigurations(true)
    initializing = false
  })

  watch(
    selected,
    () => {
      if (!initializing) {
        getConfigurations()
      }
    },
    { deep: true }
  )
</script>

<template>
  <main class="configuration">
    <h1>Product Configuration</h1>

    <p class="configuration__intro">Select your configurations below to find an available product.</p>

    <section aria-labelledby="options-heading">
      <h2 id="options-heading">Configurations</h2>

      <div v-for="option in options" :key="option.key" class="form-group">
        <label :for="option.key">{{ option.label }}</label>

        <div class="form-control-wrapper">
          <select :id="option.key" v-model="selected[option.key]" class="form-control">
            <option value="">
              Select One
            </option>

            <option v-for="value in option.values" :key="value" :value="value">
              {{ value.charAt(0).toUpperCase() + value.slice(1) }}
            </option>
          </select>

          <button v-if="selected[option.key]" type="button" class="clear-selection" :aria-label="`Clear ${option.label}`"  @click="selected[option.key] = ''">Clear</button>
        </div>
      </div>
    </section>

    <p v-if="isLoading" aria-live="polite">Loading available configurations...</p>
    <p v-else-if="errorMessage" role="alert">{{ errorMessage }}</p>
    <p v-else-if="productCount > 1" class="product-count" aria-live="polite">{{ productCount }} products match your selections.</p>
    <section v-else-if="product" class="product" aria-labelledby="product-heading">
      <h2 id="product-heading">Product</h2>

      <h3>{{ product.name }}</h3>

      <dl>
        <div>
          <dt>Part Number</dt>
          <dd>{{ product.part_number }}</dd>
        </div>

        <div>
          <dt>Price</dt>
          <dd>${{ product.price }}</dd>
        </div>
      </dl>
    </section>
    <p v-else>No products are currently available for these selections.</p>
  </main>
</template>

<style>
  :root {
    --color-text: #222;
    --color-muted: #666;
    --color-border: #ccc;
    --color-border-focus: #555;
    --color-background: #fff;
    --color-background-subtle: #f5f5f5;
    --color-link: #175ea8;
  }

  * {
    box-sizing: border-box;
  }

  body {
    font-family: Arial, Helvetica, sans-serif;
    color: var(--color-text);
    line-height: 1.5;
    background: var(--color-background);
    margin: 0;
  }

  .configuration {
    width: min(700px, calc(100% - 2rem));
    padding: 2rem 0;
    margin: 0 auto;
  }

  .configuration h1 {
    font-size: 2rem;
    margin: 0 0 0.5rem;
  }

  .configuration h2 {
    font-size: 1.25rem;
    margin: 0 0 1.5rem;
  }

  .configuration h3 {
    font-size: 1.15rem;
    margin: 0 0 1rem;
  }

  .configuration__intro {
    color: var(--color-muted);
    margin: 0 0 2rem;
  }

  .form-group {
    margin-bottom: 1.25rem;
  }

  .form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.4rem;
  }

  .form-control-wrapper {
    display: flex;
    gap: 0.5rem;
    align-items: flex-start;
  }

  .form-control {
    width: 100%;
    max-width: 400px;
    min-height: 42px;
    color: var(--color-text);
    background: #fff;
    border: 1px solid var(--color-border);
    border-radius: 3px;
    padding: 0.5rem 0.75rem;
  }

  .form-control:focus {
    outline: 2px solid var(--color-border-focus);
    outline-offset: 1px;
  }

  .clear-selection {
    color: var(--color-link);
    border: 0;
    background: transparent;
    padding: 0.5rem 0.75rem;
  }

  .clear-selection:hover {
    text-decoration: underline;
  }

  .clear-selection:focus-visible {
    outline: 2px solid var(--color-border-focus);
    outline-offset: 2px;
  }

  .product-count {
    color: var(--color-muted);
    margin: 2rem 0;
  }

  .product {
    background: var(--color-background-subtle);
    padding: 1.5rem;
    border: 1px solid var(--color-border);
  }

  .product dl {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin: 1.25rem 0 0;
  }

  .product dt {
    font-size: 0.85rem;
    color: var(--color-muted);
    margin-bottom: 0.25rem;
  }

  .product dd {
    font-weight: 600;
    margin: 0;
  }

  @media (min-width: 576px) {
    .configuration {
      padding: 3rem 0;
    }

    .form-control-wrapper {
      align-items: center;
    }

    .product dl {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
</style>
