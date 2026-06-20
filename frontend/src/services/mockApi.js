export async function getMockData(fileName) {
  const response = await fetch(`/data/${fileName}`)

  if (!response.ok) {
    throw new Error(`Unable to load ${fileName}`)
  }

  return response.json()
}
