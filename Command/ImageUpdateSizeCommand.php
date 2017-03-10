<?php

namespace EMC\FileinputBundle\Command;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Repository\RepositoryFactory;
use EMC\FileinputBundle\Entity\File;
use EMC\FileinputBundle\Entity\FileInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImageUpdateSizeCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('fileinput:image:update-size')
			->setDescription('Update base for each unknown picture size');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/* @var EntityManager $entityManager */
		$entityManager = $this->getContainer()->get('doctrine')->getManager();
		/* @var EntityRepository $repository */
		$fileClass = $this->getContainer()->getParameter('emc_fileinput.file_class');
		$repository= $entityManager->getRepository($fileClass);

		/* @var Query $query */
		$query = $repository->createQueryBuilder('f')
			->where('f.height IS NULL OR f.height = 0')
			->andWhere('f.width IS NULL OR f.width = 0')
			->getQuery()
			;
		$output->writeln('Chargement des images...');
		$images = $query->getResult();
		$count = 0;
		foreach ($images as $image) {
			if($this->update($entityManager, $image)){
				$count++;
			}
		}
		$output->writeln($count.' images modifiées');
		$entityManager->flush();
	}

	protected function update(EntityManager $entityManager, FileInterface $image)
	{
		$rootDir = $this->getContainer()->getParameter('kernel.root_dir');
		$path = sprintf('%s/../web/%s', $rootDir, $image->getPath());
		if(file_exists($path)){
			list($width, $height) = getimagesize($path);
			$image->setWidth($width);
			$image->setHeight($height);
			return true;
		}
		return false;
	}
}